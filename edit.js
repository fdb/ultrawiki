function openWindow(url, name, params) {
	newWindow = window.open('', name, params);
	newWindow.focus();
	if (newWindow.opener == null) newWindow.opener = self;
	newWindow.location.href=url;
}


function insertMediaElement() {
	openWindow('media.php', '_uw_media', 'width=750,height=400,resizable=yes,scrollbars=yes')
}

function storeCaret(area) {
	if (area.createTextRange) {
		area.caretPos = document.selection.createRange().duplicate();
	}
}

function selectMedia(fname) {
	var area = opener.document.getElementById('text');
	var media = '!' + fname + '!';
	var txt = area.value;
	if (area.createTextRange && area.caretPos) { // Code for IE
		var caretPos = area.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? media + ' ' : media;
	} else if (area.selectionStart) { // Code for Gecko
		txt = txt.substring(0,area.selectionStart) + media + txt.substring(area.selectionStart);
		area.value = txt;
	} else { // For everything else
		txt += media;
		area.value = txt;
	}
	self.close();
	area.focus();
}

function helpon() {
	document.getElementById("markguide").style.display="block";
	document.getElementById("marktoggle").className="open";
	document.getElementById("marklink").href="javascript:helpoff();";
}
function helpoff() {
	document.getElementById("markguide").style.display="none";
	document.getElementById("marktoggle").className="closed";
	document.getElementById("marklink").href="javascript:helpon();";
}
