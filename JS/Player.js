function LaunchGame(gameId,isThing) {
        var modal =
            document.getElementById(
                "ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1"
            );
        if (isThing) {modal = document.getElementById(
                "ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1" + gameId
            );
        }
        var xhr; 
        if (window.XMLHttpRequest) { xhr = new XMLHttpRequest(); } else { xhr = new ActiveXObject( "Microsoft.XMLHTTP" ); }
        if (modal) { modal.style.display = "block"; }

        Roblox.Display.Stage("Requesting");

        setTimeout(function () {
            Roblox.Display.Stage("Waiting");
        }, 300);

        setTimeout(function () {
            Roblox.Display.Stage("Loading");
        }, 700);

        setTimeout(function () {
            Roblox.Display.Stage("Joining");
            xhr.open("GET", "/Data/GetAvailableServer.php?id=" + gameId, true ); 
            xhr.send(null);
        }, 1100);

        xhr.onreadystatechange = function () { 
            if ( xhr.readyState == 4 && xhr.status == 200 ) { 
                var parts = xhr.responseText.split("|"); 
                if (parts[0] == "OK") { 
                    var auth = parts[1]; 
                    var port = parts[2]; 
                    Roblox.Utils.Launch("http://www.ryblox.xyz/Game/Join.ashx?auth=" + auth + "&port=" + port, gameId); 
                } else { 
                    alert(xhr.responseText); 
                } 
            } 
        };

        setTimeout(function () {

            if (modal) {
                modal.style.display = "none";
            }

        }, 2000);
}

    function addEvent(element, eventName, handler) {
        if (element.addEventListener) {
            element.addEventListener(
                eventName,
                handler,
                false
            );
        }
        else if (element.attachEvent) {

            element.attachEvent(
                "on" + eventName,
                handler
            );

        }
    }

    function initPage() {

        var cancelButton =
            document.getElementById("Cancel");

        if (cancelButton) {

            addEvent(
                cancelButton,
                "click",
                function () {

                    var modal =
                        document.getElementById(
                            "ctl00_cphRoblox_VisitButtons_rbxPlaceLauncher_Panel1"
                        );

                    if (modal) {
                        modal.style.display = "none";
                    }

                }
            );
        }
    }
    addEvent(window, "load", initPage);