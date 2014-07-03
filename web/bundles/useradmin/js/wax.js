function wghilight(e)
{
    e.className = 'hilight';
}

function wgwaxon(e, id)
{
	if (!document.getElementById(id) || !document.getElementById(id).checked)
		e.className = "waxon";
	else
    	e.className = "hilight";
}

function wgwaxoff(e,id)
{
	if (!document.getElementById(id) || !document.getElementById(id).checked)
		e.className = "waxoff";
	else
    	e.className = "hilight";
}

function changecolor(newcolor, frmBox) {
	document.getElementById(frmBox).style.backgroundColor = newcolor; 
	return;
}