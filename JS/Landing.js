var randomMsgs = [
    "RYDENYTE is cool!",
    "How do i make renderhook in turbowarp",
    "Dr3ddx was here",
    "it's 2009..."
];

var cooltext = document.getElementById("randomtext");

function changeMessage() {
    cooltext.style.opacity = "0";

    setTimeout(() => {
        var msg = randomMsgs[Math.floor(Math.random() * randomMsgs.length)];
        cooltext.innerHTML = msg;

        cooltext.style.opacity = "1";
    }, 500);
}

changeMessage();
setInterval(changeMessage, 10000);