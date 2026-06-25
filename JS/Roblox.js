var Roblox = Roblox || {};

Roblox.Utils = {
  Launch: function (url, gameId) {
    var isIDE = false;
    var app = null;
    var workspace = null;

    try {
      if (window.external && window.external.GetApp) {
        app = window.external.GetApp();
        isIDE = true;
      }
    } catch (e) {}

    if (!isIDE) {
      window.location.href = "/Launch.aspx?id=" + gameId;
    } else {
      try {
        workspace = app.CreateGame(2);
        workspace.ExecUrlScript(url);
      } catch (e) {
        alert(e);
      }
    }
  }
};

Roblox.Display = {
  Stage: function (stageId) {
    var stages = ["Requesting","Waiting","Loading","Joining","Error","Expired","GameEnded","GameFull"];
    var i;
    var el;

    for (i = 0; i < stages.length; i++) {
      el = document.getElementById(stages[i]);

      if (el) {
        el.style.display = "none";
      }
    }

    var active = document.getElementById(stageId);

    if (active) {
      active.style.display = "inline";
    }
  }
};
