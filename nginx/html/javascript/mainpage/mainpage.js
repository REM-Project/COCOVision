function roomdisplay(){
	//alert("ab");
	const aaa = 'echo <?=$tablename[1]?>'
	alert(aaa);
	var phpreceive = JSON.parse('<?php echo $jssend; ?>');
	var selectmenu_id = document.getElementById("selectmenu");
	alert(phpreceive[1]);
	for(var i = 0; i <= phpreceive.length; i++) {
		var room = document.createElement('option');
		room.text = phpreceive[i];
		room.value = phpreceive[i];
		selectmenu_id.appendChild(room);
	}
}
//alert(phpreceive[1]);
window.onload = roomdisplay;