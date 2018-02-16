function formRowPlus(parentid, rowid, thiselement) {
   var thisParent = document.getElementById(parentid);
   var thisRow = document.getElementById(rowid);
   //alert(thisRow.type);
   var newRow = thisRow.cloneNode(true);
   //k = thisParent.childNodes
   for (i = 0; i < thisRow.childNodes.length; i++) {
      //'button,'checkbox','file','hidden',image','password','radio','reset','select-one','select-multiple','submit','text','textarea'
      //alert(thisRow.childNodes[i].id + ' ' + thisRow.childNodes[i].index);
   }
   thisParent.appendChild(newRow);
}


function formRowMinus(parentid, rowNode) {
   var thisParent = document.getElementById(parentid);
   for (i = 0; i < thisParent.childNodes.length; i++) {
      if (thisParent.childNodes[i] == rowNode) {
         thisParent.removeChild(thisParent.childNodes[i]);
      }
   }
}
