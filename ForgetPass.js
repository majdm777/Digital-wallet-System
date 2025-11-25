let time;
let message = document.getElementById("request-message");
let countdown = document.getElementById("countdown");
let button = document.getElementById("request-button");
let timerId;

function update(){
    if(time<=0){
        
        button.style.backgroundColor="white"
        message.innerHTML=""
        countdown.innerHTML=""
        clearInterval(timerId)
        button.disabled=false
        retrun;
    }
    time--
    countdown.innerHTML=time
}

function sendver(){

    let emailInput = document.getElementById("email").value;
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; 
    let email=localStorage.getItem("email")
    if (!emailPattern.test(emailInput)) {
        alert("Please enter a valid email address.");
        return; 
    }
    if(emailInput!=email){
        alert("Could Not find your account");
        return;        
    }
    time=30;
    button.style.backgroundColor="rgba(194,181,173,255)"
    button.disabled=true
    message.innerHTML="you can re-send a message after:"
    countdown.innerHTML=time

    timerId=setInterval(update,1000)
    return;
}

