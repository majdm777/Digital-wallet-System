function show_icon(){
    var show = document.querySelector(".show-password");
    var password = document.getElementById("password");
    
    if(password.value!="" && password.type=== "password"){
        show.textContent="show"
    } else if(password.value!="" && password.type === "text"){
        show.textContent="hide"
    }
    else{
        show.textContent=""
        password.type = "password";
    }

}


function resetMessage() {
    var messageElement = document.getElementById("validation-message");
    if (messageElement) {
        messageElement.textContent = "";
    }
}
 

// let info=document.getElementsByClassName("input-box")
// let mess=document.getElementById("validation-message")
// function checkacc(){
//    // mess.innerText ="hi"
//     let email=localStorage.getItem("email")
//     let pass=localStorage.getItem("password")
//     if(info[0].value!=email){
//         mess.innerText="invalid email"

//         return;
//     }
//     if(info[1].value!=pass){
//         mess.innerText="invalid password"
//         return;
//     }
//     mess.innerText=""
//     window.location.href="Main.html"

// }

function Show_password() {
    var password = document.getElementById("password");
    var show = document.querySelector(".show-password");
    if(password.value!=""){
        if (password.type === "password") {
            password.type = "text";
            show.textContent = "hide";  
        } else {
            password.type = "password";
            show.textContent = "show";  
        }
    }
}
