// Copyright Zikula, licensed MIT.

var plumbInstance;
var currentZoom;

function updateZoomLevel(factor) {
    if (factor === 0) {
        currentZoom = 1;
    } else {
        currentZoom += factor;
    }
    jQuery('.jtk-canvas').css({
        '-webkit-transform': 'scale(' + currentZoom + ')',
        '-moz-transform': 'scale(' + currentZoom + ')',
        '-ms-transform': 'scale(' + currentZoom + ')',
        '-o-transform': 'scale(' + currentZoom + ')',
        'transform': 'scale(' + currentZoom + ')'
    });
    jsPlumb.setZoom(currentZoom);
}

function initZoomTools() {
    jQuery('#decreaseZoomLevel').click(function (event) {
        updateZoomLevel(-0.1);
    });
    jQuery('#resetZoomLevel').click(function (event) {
        updateZoomLevel(0);
    });
    jQuery('#increaseZoomLevel').click(function (event) {
        updateZoomLevel(0.1);
    });
    updateZoomLevel(0);
}

var configSection = 'workflow'; // change to framework in master branch

var regenerateOutput = function () {
    var oneIndent, indent, output, states, transitions, connections;

    oneIndent = '   ';
    indent = oneIndent;
    output = {
        yaml: [],
        xml: []
    };

    states = jQuery('.jtk-canvas .node.state');
    transitions = jQuery('.jtk-canvas .node.transition');

    output.xml.push('<?xml version="1.0" encoding="utf-8" ?>');
    output.xml.push('<container xmlns="http://symfony.com/schema/dic/services"');
    output.xml.push(indent + 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"');
    output.xml.push(indent + 'xmlns:framework="http://symfony.com/schema/dic/symfony"');
    output.xml.push(indent + 'xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd');
    output.xml.push(indent + indent + 'http://symfony.com/schema/dic/symfony http://symfony.com/schema/dic/symfony/symfony-1.0.xsd"');
    output.xml.push('>');
    output.xml.push('');

    output.yaml.push(configSection + ':');
    output.yaml.push(indent + 'workflows:');
    output.xml.push(indent + '<framework:config>');
    indent += oneIndent;

    output.yaml.push(indent + jQuery('#workflowName').val() + ':');
    output.xml.push(indent + '<framework:workflow name="' + jQuery('#workflowName').val() + '" type="' + jQuery('#workflowType').val() + '">');

    indent += oneIndent;
    output.yaml.push(indent + 'type: ' + jQuery('#workflowType').val());
    output.yaml.push(indent + 'marking_store:');
    output.yaml.push(indent + oneIndent + 'type: ' + jQuery('#markingStoreType').val());
    if ('method' === jQuery('#markingStoreType').val()) {
        output.yaml.push(indent + oneIndent + 'property: ' + jQuery('#markingStoreField').val());
        output.xml.push(indent + '<framework:marking-store>');
        output.xml.push(indent + oneIndent + '<framework:type>' + jQuery('#markingStoreType').val() + '</framework:type>');
        output.xml.push(indent + oneIndent + '<framework:property>' + jQuery('#markingStoreField').val() + '</framework:property>');
    } else {
        // deprecated, but kept for covering other marking store types in a general way
        output.yaml.push(indent + oneIndent + 'arguments:');
        output.yaml.push(indent + oneIndent + oneIndent + '- ' + jQuery('#markingStoreField').val());
        output.xml.push(indent + '<framework:marking-store type="' + jQuery('#markingStoreType').val() + '">');
        output.xml.push(indent + oneIndent + '<framework:arguments>' + jQuery('#markingStoreField').val() + '</framework:arguments>');
    }
    output.xml.push(indent + '</framework:marking-store>');
    output.xml.push('');

    output.yaml.push(indent + 'supports:');
    jQuery.each(jQuery('#supportedEntities').val().split("\n"), function (lineIndex, entityName) {
        if (entityName.trim()) {
            output.yaml.push(indent + oneIndent + '- ' + entityName.trim());
            output.xml.push(indent + '<framework:support>' + entityName.trim() + '</framework:support>');
        }
    });
    output.xml.push('');

    output.yaml.push(indent + 'places:');
    states.each(function (index) {
        output.yaml.push(indent + oneIndent + '- ' + jQuery(this).text());
        output.xml.push(indent + '<framework:place>' + jQuery(this).text() + '</framework:place>');
    });
    output.xml.push('');

    output.yaml.push(indent + 'transitions:');
    transitions.each(function (index) {
        output.yaml.push(indent + oneIndent + jQuery(this).text() + ':');
        output.xml.push(indent + '<framework:transition name="' + jQuery(this).text() + '">');
        connections = plumbInstance.select({ target: jQuery(this).attr('id') });
        if (connections.length > 0) {
            if (connections.length > 1) {
                var sourceStates = '';
                connections.each(function (connection) {
                    if (sourceStates !== '') {
                        sourceStates += ', ';
                    }
                    sourceStates += jQuery('#' + connection.sourceId).text();
                    output.xml.push(indent + oneIndent + oneIndent + '<framework:from>' + jQuery('#' + connection.sourceId).text() + '</framework:from>');
                });
                output.yaml.push(indent + oneIndent + oneIndent + 'from: [' + sourceStates + ']');
            } else {
                output.yaml.push(indent + oneIndent + oneIndent + 'from: ' + jQuery('#' + connections.get(0).sourceId).text());
                output.xml.push(indent + oneIndent + oneIndent + '<framework:from>' + jQuery('#' + connections.get(0).sourceId).text() + '</framework:from>');
            }
            output.xml.push('');
            connections = plumbInstance.select({ source: jQuery(this).attr('id') });
            if (connections.length > 0) {
                // use only first outgoing connection (as a transition may only lead to one state)
                output.yaml.push(indent + oneIndent + oneIndent + 'to: ' + jQuery('#' + connections.get(0).targetId).text());
                output.xml.push(indent + oneIndent + oneIndent + '<framework:to>' + jQuery('#' + connections.get(0).sourceId).text() + '</framework:to>');
            }
        }
        output.xml.push(indent + '</framework:transition>');
    });

    output.xml.push(oneIndent + oneIndent + '</framework:workflow>');
    output.xml.push('');
    output.xml.push(oneIndent + '</framework:config>');
    output.xml.push('');
    output.xml.push('</container>');

    jQuery('#outputYaml pre code').html(output.yaml.join("\n"));
    jQuery('#outputXml pre code').text(output.xml.join("\n"));
};

function initDiagramEventListeners() {
    plumbInstance.bind('connection', function (info, originalEvent) {
        regenerateOutput();
    });
    plumbInstance.bind('connectionDetached', function (info, originalEvent) {
        regenerateOutput();
    });
    plumbInstance.bind('click', jsPlumb.deleteConnection);
    plumbInstance.bind('beforeDrop', function (info) {
        var sourceNode, targetNode, existingOutgoingConnections;

        sourceNode = jQuery('#' + info.sourceId);
        targetNode = jQuery('#' + info.targetId);

        // returning false or nothing aborts the connection and removes it from the UI

        if (sourceNode.hasClass('state')) {
            if (!targetNode.hasClass('transition')) {
                alert(Translator.trans('States may only lead to transitions.'));

                return false;
            }

            return true;
        }
        if (sourceNode.hasClass('transition')) {
            if (!targetNode.hasClass('state')) {
                alert(Translator.trans('Transitions may only lead to states.'));

                return false;
            }

            existingOutgoingConnections = plumbInstance.select({ source: sourceNode });
            if (existingOutgoingConnections.length > 0) {
                alert(Translator.trans('Transitions may only lead to one single state.'));

                return false;
            }

            return true;
        }

        return true;
    });
}

var addNodeTools = function(node) {
    node.append('<p class="node-tools"><i class="ep fas fa-exchange" title="' + Translator.trans('Add connection') + '"></i><i class="fas fa-trash-alt pointer" title="' + Translator.trans('Remove element') + '"></i></p>');
    node.find('i.fa-trash-alt').click(function (event) {
        plumbInstance.remove(node.attr('id'));
        regenerateOutput();
    });
};

var cleanName = function (name) {
    return name.replace(/[^\w\s]/gi, '').replace(/ /g, '_').toLowerCase();
};

var addNode = function () {
    jQuery('#nodeModalLabel').text(Translator.trans('Add node'));
    jQuery('#nodeName').val('');
    jQuery('#nodeTypeSelection').removeClass('d-none');
    jQuery('#nodeTypeSelection input').prop('required', true);

    jQuery('#nodeModal .modal-footer .btn-primary').text(Translator.trans('Create')).unbind('click').click(function (event) {
        var name, uniqueName, nameSuffix, nodeType, node;

        nodeType = jQuery('#nodeTypeSelection input:checked').val();
        name = jQuery('#nodeName').val();
        if (nodeType !== null && nodeType !== '' && name !== null && name !== '') {
            name = cleanName(name);
            uniqueName = name;
            nameSuffix = 0;
            while (jQuery('#state' + uniqueName + ', #transition' + uniqueName).length > 0) {
                uniqueName = name + ++nameSuffix;
            }
            jQuery('#canvas').append('<div class="node ' + nodeType + '" id="' + nodeType + uniqueName + '">' + uniqueName + '</div>');
            node = jQuery('#' + nodeType + uniqueName);
            node.css({ left: '50px', top: '50px' });
            initNode(node);
            regenerateOutput();
        }
        jQuery('#nodeModal').modal('hide');
    });
    jQuery('#nodeModal').modal('show');
};
var editNode = function (event) {
    var node;

    node = jQuery(this);
    if (node.hasClass('state')) {
        jQuery('#nodeModalLabel').text(Translator.trans('Edit state'));
    } else if (node.hasClass('transition')) {
        jQuery('#nodeModalLabel').text(Translator.trans('Edit transition'));
    }
    jQuery('#nodeName').val(node.text());

    jQuery('#nodeTypeSelection').addClass('d-none');
    jQuery('#nodeTypeSelection input').removeProp('required');

    jQuery('#nodeModal .modal-footer .btn-primary').text(Translator.trans('Update')).unbind('click').click(function (event) {
        var name, uniqueName, nameSuffix, newId;

        name = jQuery('#nodeName').val();
        if (name !== null && name !== '') {
            name = cleanName(name);
            uniqueName = name;
            nameSuffix = 0;
            while (jQuery('#state' + uniqueName + ', #transition' + uniqueName).not(node).length > 0) {
                uniqueName = name + ++nameSuffix;
            }

            node.html(uniqueName);
            addNodeTools(node);

            newId = '';
            if (node.hasClass('state')) {
                newId = 'state' + uniqueName;
            } else if (node.hasClass('transition')) {
                newId = 'transition' + uniqueName;
            }
            jsPlumb.setId(node, newId);
            regenerateOutput();
        }
        jQuery('#nodeModal').modal('hide');
    });
    jQuery('#nodeModal').modal('show');
};

var initNode = function (node) {
    // initialise draggable element
    plumbInstance.draggable(node, {
        grid: [50, 50],
        snapThreshold: 0
    });

    // node-specific events
    node.dblclick(editNode);

    addNodeTools(node);

    // initialise element as connection targets and source.
    plumbInstance.makeSource(node, {
        filter: '.ep',
        extract: {
            'action': 'the-action'
        }
    });
    plumbInstance.makeTarget(node, {
        allowLoopback: false
    });
};

jsPlumb.ready(function () {
    initZoomTools();

    plumbInstance = jsPlumb.getInstance({
        Anchor: 'Continuous',
        ConnectionOverlays: [
            ['Arrow', {
                location: 1,
                visible: true,
                width: 15,
                length: 12,
                foldback: 0.8
            }]
        ],
        Connector: ['Flowchart', {
            stub: [40, 60],
            gap: 4,
            cornerRadius: 5
        }],
        Container: 'canvas',
        DragOptions: {
            cursor: 'pointer',
            zIndex: 2000
        },
        DropOptions: {
            tolerance: 'touch',
            hoverClass: 'drop-hover',
            activeClass: 'drag-active'
        },
        Endpoint: ['Dot', {
            radius: 3
        }],
        PaintStyle: {
            strokeWidth: 2,
            stroke: '#a4a8ab',
            joinstyle: 'round',
            outlineWidth: 2,
            outlineStroke: 'white'
        },
        HoverPaintStyle: {
            strokeWidth: 3,
            stroke: '#8f9fb0',
            outlineWidth: 5,
            outlineStroke: 'white'
        },
        MaxConnections: -1,
        ReattachConnections: true
    });

    var allNodes = jQuery('.jtk-canvas .node');
    var allConnections = jQuery('#connectionList li');

    // suspend drawing until all elements are initialised
    plumbInstance.batch(function () {

        // simple arrangement of existing nodes
        var left = 50, top = 50;
        allNodes.each(function (index) {
            jQuery(this).css({
                left: left + 'px',
                top: top + 'px'
            });
            left += 250;
            if (left > 700) {
                left = 50;
                top += 150;
            }
        });

        initDiagramEventListeners();

        // add existing nodes
        allNodes.each(function (index) {
            initNode(jQuery(this));
        });

        // add existing connections
        allConnections.each(function (index) {
            plumbInstance.connect({
                source: jQuery(this).data('from'),
                target: jQuery(this).data('to')/*,
                    editable: true*/
/*                          overlays:[
    ["Custom", {
      create:function(component) {
        return jQuery("<select id='myDropDown'><option value='foo'>foo</option><option value='bar'>bar</option></select>");
      },
      location:0.7,
      id:"customOverlay"
    }]
  ],*/
            });
        });
    });

    jQuery('#addNode').click(addNode);

    jQuery('#workflowName').on('change keypress', function (event) {
        if ('' === jQuery(this).val()) {
            jQuery(this).val('my_workflow');
        }
        jQuery(this).val(cleanName(jQuery(this).val()));
        regenerateOutput();
    });
    jQuery('#workflowType').change(function (event) {
        if ('workflow' === jQuery(this).val()) {
            jQuery('#markingStoreType').prop('disabled', false);
        } else if (jQuery(this).val() === 'state_machine') {
            jQuery('#markingStoreType').val('method').prop('disabled', true);
        }
        regenerateOutput();
    });
    jQuery('#markingStoreType').change(function (event) {
        if ('multiple_state' === jQuery(this).val()) {
            jQuery('#markingStoreFieldType').text(Translator.trans('array'));
        } else if ('single_state' === jQuery(this).val()) {
            jQuery('#markingStoreFieldType').text(Translator.trans('string'));
        }
        regenerateOutput();
    });
    jQuery('#markingStoreType').trigger('change');
    jQuery('#markingStoreField').on('change keypress', function (event) {
        if ('' === jQuery(this).val()) {
            jQuery(this).val('state');
        }
        jQuery(this).val(cleanName(jQuery(this).val()));
        regenerateOutput();
    });
    jQuery('#supportedEntities').on('change keypress', regenerateOutput);
    jQuery('#workflowType').trigger('change');
});
