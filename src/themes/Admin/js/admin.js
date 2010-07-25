/* Prototype User Drop Down start */
var zkdropdownShowingUpDuration = 0.5;
var zkdropdownHidingDuration = 0.3;
var zkdropdownHideDelay = 0.1;

function zkdropdownShowingUpEffect(element){
	if(!element.visible()){
		new Effect.BlindDown(element, {
			duration: zkdropdownShowingUpDuration,
			queue: {
				position: 'end',
				scope: element.identify(),
				limit:2
			}
		});
	}
}

function zkdropdownHidingEffect(element){
	new Effect.BlindUp(element, {
		duration: zkdropdownHidingDuration,
		queue: {
			position: 'end',
			scope: element.identify(),
			limit: 2
		}
	 });
}

function setDelayedHide(element){
	element.addClassName('waitingtohide')
	if(!element.hasClassName('hidding')){
		if (!element.hasClassName('hiddingtimerset')){	
			element.addClassName('hiddingtimerset');
			setTimeout(function(){ delayedHide(element); }, zkdropdownHideDelay * 1000);
		}
	}
}
function delayedHide(dropElement){
	dropElement.removeClassName('hiddingtimerset');
	if (dropElement.hasClassName('waitingtohide')){
		zkdropdownHidingEffect(dropElement);
		dropElement.addClassName('hidding');
		setTimeout(
			function(){
				dropElement.removeClassName('waitingtohide');
				dropElement.removeClassName('hidding');
				dropElement.removeClassName('active');
			}, zkdropdownHidingDuration * 1000);
	}
}

function linkMouseOut(id){
	var dropElement = id.element().next();		
	if (dropElement && dropElement.hasClassName('active')){
		setDelayedHide(dropElement);
	}
}
function linkMouseOver(id){
	var dropElement = id.element().next();
	if(dropElement){
		if (!dropElement.hasClassName('hidding')){
			dropElement.removeClassName('waitingtohide');
		}
		if (!dropElement.hasClassName('active')){
			dropElement.addClassName('active');
			zkdropdownShowingUpEffect(dropElement);
		}
	}
}
function submenuMouseOut(event){
	var dropElement = event.findElement("ul");	
	if (dropElement && dropElement.hasClassName('active')){
		setDelayedHide(dropElement);
	}
}

function submenuMouseOver(event){
	var dropElement = event.findElement("ul");	
	if (dropElement && !dropElement.hasClassName('hidding')){
		dropElement.removeClassName('waitingtohide');
	}
}

document.observe('dom:loaded', function() {
	$$('a.zk-drop').each(function(name) {
		name.observe('mousemove', linkMouseOver.bindAsEventListener(this));
		name.observe('mouseout',  linkMouseOut.bindAsEventListener(this));
	});

	$$('ul.zkdropper').each(function(name){
		name.observe('mousemove', submenuMouseOver.bindAsEventListener(this));
		name.observe('mouseout',  submenuMouseOut.bindAsEventListener(this));
	});
})
/* User DropDown end */