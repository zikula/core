
var Category = {};

Category.Attributes = {};

Category.Attributes.Add = function(inputElement)
{
  if ($('new_attribute_name').value == '' || $('new_attribute_value').value == '')
    return false;

  var td = inputElement.parentNode;
  var tr = td.parentNode;
  var table = tr.parentNode.parentNode;
  
  var newRow = table.insertRow(tr.rowIndex+1);
  
  var newTd1 = newRow.insertCell(0);
  var newInput1 = document.createElement("input");
  newInput1.name = "attribute_name[]";
  newInput1.value = $('new_attribute_name').value;
  $('new_attribute_name').value = '';
  newTd1.appendChild(newInput1);

  var newTd2 = newRow.insertCell(1);
  var newInput2 = document.createElement("input");
  newInput2.name = "attribute_value[]";
  newInput2.value = $('new_attribute_value').value;
  newInput2.size = $('new_attribute_value').size;
  $('new_attribute_value').value = '';
  newTd2.appendChild(newInput2);

  var newTd3 = newRow.insertCell(2);
  var newInput3 = document.createElement("input");
  newInput3.type = 'image';
  newInput3.src = 'images/icons/extrasmall/edit_remove.gif';
  newInput3.onclick = function() { return Category.Attributes.Remove(newInput3); }
  newTd3.appendChild(newInput3);

  $('new_attribute_name').focus();

  return false;
}


Category.Attributes.Remove = function(inputElement)
{
  var td = inputElement.parentNode;
  var tr = td.parentNode;
  var table = tr.parentNode.parentNode;
  table.deleteRow(tr.rowIndex);
  return false;
}

