
var search_allItems = new Array();
var search_currentItem = null;
var search_query = null;

function search_loadItem(item_id)
{
	if (item_id == null)
		return;

	try {
		if (prev_current != -1)
			document.getElementById('item' + prev_current).setAttribute("class", "line floatBox");
	}
	catch (ex)
	{
	}

	var pprev = prev_current;
	prev_current = item_id;
	document.getElementById('item' + prev_current).setAttribute("class", "line floatBox selectFloatBox");

	search_onItemLoadEvent(item_id);
	search_currentItem = item_id;
}



function private_search_getPreviousItem(id)
{
	for (var i = 0; i < search_allItems.length; i++)
	{
		if (search_allItems[i] == id)
		{
			if (i-1 in search_allItems)
				return search_allItems[i-1];
			else
				return null;
		}
	}
	return null;
}

function private_search_getNextItem(id)
{
	for (var i = 0; i < search_allItems.length; i++)
	{
		if (search_allItems[i] == id)
		{
			if (i+1 in search_allItems)
				return search_allItems[i+1];
			else
				return null;
		}
	}
	return null;
}

function search_loadPreviousMessage()
{
	if (search_allItems.length <= 1)
		return;
	var msg = private_search_getPreviousItem(search_currentItem);
	if (msg == null)
		msg = search_allItems[search_allItems.length-1];
	search_loadItem(msg);
}

function search_loadNextMessage()
{
	if (search_allItems.length <= 1)
		return;
	var msg = private_search_getNextItem(search_currentItem);
	if (msg == null)
		msg = search_allItems[0];
	search_loadItem(msg);
}

function search_deleteCurrentItem()
{
	search_deleteItem(search_currentItem);
}

function search_deleteItem(item)
{
	var url = '/search_query_ajax/' + search_query + '/delete/' + item;
	var containerid = 'allItems';



	var page_request = false
	if (window.XMLHttpRequest) // if Mozilla, Safari etc
	page_request = new XMLHttpRequest()
	else if (window.ActiveXObject){ // if IE
	try {
	page_request = new ActiveXObject("Msxml2.XMLHTTP")
	} 
	catch (e){
	try{
	page_request = new ActiveXObject("Microsoft.XMLHTTP")
	}
	catch (e){}
	}
	}
	else
	return false
	page_request.onreadystatechange=function(){
		if (page_request.readyState == 4 && (page_request.status==200 || window.location.href.indexOf("http")==-1))
		{
			document.getElementById(containerid).innerHTML=page_request.responseText;
			search_loadNextMessage();
			private_search_refreshArrayData();

			if (search_allItems.length <= 0)
				search_unselectItem();
		}
	}
	if (bustcachevar) //if bust caching of external page
	bustcacheparameter=(url.indexOf("?")!=-1)? "&"+new Date().getTime() : "?"+new Date().getTime()
	page_request.open('GET', url+bustcacheparameter, true)
	page_request.send(null)

}

function private_search_refreshArrayData()
{
	search_allItems = new Array();
	var ele = document.getElementById("allItems");
	var items = ele.getElementsByClassName("emailItem");
	
	for (var i = 0; i < items.length; i++)
	{
		var id = items[i].getAttribute("id");
		search_allItems.push(id.substr(4));
	}
}