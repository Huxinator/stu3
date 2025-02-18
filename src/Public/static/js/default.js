mf = 0;
var buildmode = 0;

function onmousewheel(element, callback) {

        // @author    Andrea Giammarchi        [http://www.devpro.it/]
        // @license    MIT                 [http://www.opensource.org/licenses/mit-license.php]
        // @credits    Adomas Paltanavicius         [http://adomas.org/javascript-mouse-wheel/]

        function __onwheel(event) {
                var delta = 0;
                if (event.wheelDelta) {
                        delta = event.wheelDelta / 120;
                        if (window.opera)
                                delta = -delta;
                }
                else if (event.detail)
                        delta = -event.detail / 3;
                if (delta)
                        callback.call(element, delta);
                if (event.preventDefault)
                        event.preventDefault();
                event.returnValue = false;
                return false;
        };

        if (element.addEventListener && !window.opera)
                element.addEventListener("DOMMouseScroll", __onwheel, false);
        else
                element.onmousewheel = (function (base) {
                        return function (evt) {
                                if (!evt) evt = window.event;
                                if (base) base.call(element, evt);
                                return __onwheel(evt);
                        }
                })(element.onmousewheel);
};

function closePopup() {
        if (over) {
                cClick();
                over = null;
        }
}

function kpListener(e) {
        if (!e) e = window.event; // Drecks IE
        if (e.keyCode == 27) {
                if (buildmode == 1) {
                        setTimeout("closeBuildingInfo()", 100);
                        field_observer = 0;
                }
                if (over) {
                        closePopup();
                }
        }
}
window.onkeydown = kpListener;

function closeAjaxWindow() {
        if (buildmode == 1) {
                closeBuildingInfo();
        }
        if (over) {
                cClick();
                over = null;
        }
}

function ajaxcall(div, url) {
        new Ajax.Updater(div, url,
                {
                        evalScripts: true
                });
}

function ajaxrequest(url) {
        new Ajax.Request(url);
}

function openWindow(elt, exclusive, width) {
        if (width) {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        } else {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        }
}
function openWindowPosition(elt, exclusive, width, posx, posy) {
        if (width) {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, RELX, posx, RELY, posy, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', WIDTH, width, RELX, posx, RELY, posy, BGCOLOR, '#8897cf', FGCOLOR, '#000000', TEXTCOLOR, '#8897cf', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        } else {
                if (exclusive) {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', RELX, posx, RELY, posy, TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', BGCOLOR, '#8897cf', RELX, posx, RELY, posy, TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
                }
        }
}

function ignoreUser(obj, userid) {
        ajax_update(obj, '/pm.php?B_IGNORE_USER=1&SHOW_IGNORE=1&recid=' + userid);
}
function addUserContact(obj, userid) {
        var contact = 'selector_' + obj;
        if (!$(contact)) {
                mode = document.forms[0].elements[contact].value;
        } else {
                mode = $(contact).value;
        }
        ajax_update(obj, '/pm.php?B_ADD_CONTACT=1&SHOW_CONTACT_MODE=1&recid=' + userid + '&clmode=' + mode + '&cldiv=' + obj);
}
var clmodeswitchdiv = '';
function showContactModeSwitcher(div, contactid) {
        clmodeswitchdiv = div;
        elt = 'clmswitcher';
        openWindow(elt, 0, 70);
        ajax_update('clmswitcher', '/pm.php?SHOW_CONTACT_MODESWITCH=1&cid=' + contactid);
}
function switchContactMode(contactid, mode) {
        ajax_update(clmodeswitchdiv, '/pm.php?B_CHANGE_CONTACTMODE=1&SHOW_CONTACT_MODE=1&cid=' + contactid + '&clmode=' + mode + "&cldiv=" + clmodeswitchdiv);
        clmodeswitcdiv = '';
        cClick();
}
function addLoadEvent(func) {
        var oldonload = window.onload;
        if (typeof window.onload != 'function') {
                window.onload = func;
        }
        else {
                window.onload = function () {
                        oldonload();
                        func();
                }
        }
}
function startUp() {
        startServerTimer();
}
var servertime = 0;
function startServerTimer() {
        if (servertime == 0) {
                servertime = new Date($('servertime').innerHTML);
        }
        var hours = servertime.getHours();
        var minutes = servertime.getMinutes();
        var seconds = servertime.getSeconds();
        servertime.setSeconds(seconds + 1);
        if (hours <= 9) hours = "0" + hours;
        if (minutes <= 9) minutes = "0" + minutes;
        if (seconds <= 9) seconds = "0" + seconds;
        dispTime = hours + ":" + minutes + ":" + seconds;
        $('servertime').innerHTML = dispTime;
        $('servertime').show();
        setTimeout("startServerTimer()", 1000);

}
var selectedFieldType = 0;
var tmpfield = 0;
function ajax_update(elt, url) {
        new Ajax.Updater(elt, url, { method: 'get', evalScripts: true });
}
function showMapBy(path) {
        ajax_update('elt', path);
}
function refreshMapSectionNav(path) {
        ajax_update('map_section_nav', path);
}
function selectMapFieldType(type) {
        $('fieldtypeselector').innerHTML = '<img src="' + gfx_path + '/map/' + type + '.png" />';
        selectedFieldType = type;
}
function toggleMapfieldType(obj) {
        if (selectedFieldType == 0) {
                return;
        }
        if (tmpfield == 0) {
                tmpfield = obj.parentNode.style.backgroundImage;
                obj.parentNode.style.backgroundImage = "url(" + gfx_path + "/map/" + selectedFieldType + ".png)";
                return;
        }
        obj.parentNode.style.backgroundImage = tmpfield;
        tmpfield = 0;
        return;
}
function setNewFieldType(obj, fieldid) {
        if (selectedFieldType == 0) {
                alert("Es wurde kein Feldtyp ausgewaehlt");
                return;
        }
        ajax_update(false, '/admin/?B_EDIT_FIELD=1&field=' + fieldid + '&type=' + selectedFieldType);
        obj.parentNode.style.backgroundImage = "url(" + gfx_path + "/map/" + selectedFieldType + ".png)";
        tmpfield = obj.parentNode.style.backgroundImage;
}

function openSystemFieldSelector(fieldid) {
        elt = 'fieldselector';
        openWindow(elt, 0);
        ajax_update(elt, '/admin/?SHOW_SYSTEM_EDITFIELD=1&field=' + fieldid);
}
function selectNewSystemMapField(fieldid, cx, cy, typeid, type) {
        ajax_update(false, '/admin/?B_EDIT_SYSTEM_FIELD=1&field=' + fieldid + '&type=' + typeid);
        field = $(cx + '_' + cy);
        field.style.backgroundImage = "url(" + gfx_path + "/map/" + type + ".png)";
        closeAjaxWindow();
}
function findObject(obj) {
        var curleft = curtop = 0;
        if (obj.offsetParent) {
                do {
                        curleft += obj.offsetLeft;
                        curtop += obj.offsetTop;
                } while (obj = obj.offsetParent);
                return [curleft, curtop];
        }
}
function openTopicSettings(obj, tid, bid) {
        var pos = findObject(obj);
        elt = 'topicaction';
        openWindowPosition(elt, 1, 200, pos[0] - 200, pos[1]);
        ajax_update(elt, "alliance.php?SHOW_TOPIC_SETTINGS=1&tid=" + tid + "&bid=" + bid);
}
function openBoardSettings(obj, bid) {
        var pos = findObject(obj);
        elt = 'boardaction';
        openWindowPosition(elt, 1, 200, pos[0] - 200, pos[1]);
        ajax_update(elt, "alliance.php?SHOW_BOARD_SETTINGS=1&bid=" + bid);
}
function openPmWindow(fromId, toId, fromType, toType) {
        elt = 'pmwindow';
        openWindowPosition(elt, 1, 600, 90, 60);
        ajax_update(elt, '/pm.php?SHOW_WRITE_QUICKPM=1&fromid=' + fromId + '&toid=' + toId + '&fromtype=' + fromType + '&totype=' + toType);
}
function sendQuickPM(userId) {
        var elem = $('quickpm').serialize() + '&sstr=' + $('pm_sstr').value;
        ajaxPost('/pm.php', 'B_WRITE_PM=1&recipient=' + userId + "&" + elem);
        $('quickpm_compose').hide();
        $('quickpm_done').show();
}
function ajaxPostUpdate(destelement, url, elements) {
        new Ajax.Updater(destelement, url,
                {
                        method: 'post',
                        parameters: elements
                });
}
function ajaxPost(url, elements) {
        new Ajax.Request(url,
                {
                        method: 'post',
                        parameters: elements
                });
}
function showResearchDetails(researchId) {
        elt = 'researchwin';
        openWindow(elt);
        ajax_update(elt, '/research.php?SHOW_RESEARCH=1&id=' + researchId);
}
function openNotes() {
        str = "notes.php";
        Win = window.open(str, 'WinNotes', 'width=850,height=700,resizeable=no,location=no,scrollbars=yes,status=no');
        Win.opener = self;
}
function goToUrl(url) {
        window.location.href = url;
}
function openNewTab(url) {
        window.open(url, '_blank');
}
function toggleTableRowVisible(id) {
        if ($(id).style.display == 'block' || $(id).style.display == 'table-row') {
                $(id).style.display = 'none';
                return;
        }
        $(id).style.display = 'table-row';
}
function openPJsWin(elt, exclusive, width, offsety) {
        if (width) {
                var OLWIDTH = ' WIDTH, ' + width;
        } else {
                var OLWIDTH = '';
        }
        if (exclusive) {
                if (offsety) {
                        return overlib('<div id=' + elt + '></div>', OLWIDTH, BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, OFFSETY, offsety, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                } else {
                        return overlib('<div id=' + elt + '></div>', OLWIDTH, BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, STICKY, DRAGGABLE, ALTCUT, EXCLUSIVE);
                }
        }
        else {
                return overlib('<div id=' + elt + '></div>', OLWIDTH, BGCOLOR, '#8897cf', TEXTCOLOR, '#8897cf', FGCOLOR, '#000000', CELLPAD, 0, 0, 0, 0, HAUTO, VAUTO, DRAGGABLE, ALTCUT, STICKY);
        }
}
function cp(obj, file, ending = 'png') {
        document.images[obj].src = gfx_path + "/" + file + "." + ending;
}
function updatePMNavlet() {
        new Ajax.Request(
                '/pm.php',
                {
                        method: 'post',
                        parameters: 'SHOW_NEW_PM=1',
                        onFailure: function (e) {
                        },
                        onSuccess: function (request) {
                                div = 'navlet_newpm';
                                $(div).innerHTML = request.responseText;
                                $(div).innerHTML.evalScripts();
                        }
                }
        );
        setTimeout('updatePMNavlet()', 60000);
}
function toggleVisible(id) {
        if ($(id).style.display == 'block') {
                $(id).style.display = 'none';
                return;
        }
        $(id).style.display = 'block';
}
function showAchievement(text) {
        var elem = new Element("div");
        $(elem).addClassName('achievementbox box boxshadow');
        var header = new Element("div");
        $(header).addClassName("box_title");
        $(header).innerHTML = 'Neue Errungenschaft!';
        $(elem).appendChild(header);
        var body = new Element("div");
        $(body).addClassName("box_body");
        $(body).innerHTML = text;
        $(elem).appendChild(body);
        var close = new Element("div");
        $(close).addClassName("closebutton");
        $(close).innerHTML = 'X';
        $(close).observe("click", function () {
                Effect.Fade(this.up());
        });
        $(close).addClassName('action');
        $(elem).appendChild(close);
        document.body.appendChild(elem);
        elem.show();
}
function nodelistToString(list) {
        if (!RadioNodeList.prototype.isPrototypeOf(list)) {
                if (list.checked) {
                        return list.value
                } else {
                        return '';
                }
        }
        return Array.from(list)
                .filter(function (node) {
                        if (node.checked) {
                                return true;
                        }
                        return false;
                })
                .map(function (node) {
                        return node.value;
                })
                .join(',');
}
function snafu(colonyId, action, mode, sstr) {
        commodityId = $('commodityselector').getValue();
        goToUrl('/colony.php?id=' + colonyId + '&' + action + '=1&mode=' + mode + '&selection=' + commodityId + '&sstr=' + sstr);
}
