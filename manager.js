let managerId;

function send(action, extraData = {}) {
    return fetch("managerData.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action, ...extraData })
    }).then(res => res.json());
}

function searchUser() {
    const userId = parseInt(document.getElementById("userIdSearch").value);
    if (!userId){
      document.getElementById("userInfo").style.display = "none";
      return alert("Invalid ID")
    }
    send("searchUser", { userId }).then(data => {

        if (data.error) {
            document.getElementById("error").style.display = "block";
            document.getElementById("error").innerText = data.error;
            document.getElementById("userInfo").style.display = "none";
            return;
        }

        document.getElementById("error").style.display = "none";
        document.getElementById("userInfo").style.display = "block";

        // displayUserId.innerText = data.userId;
        // displayUserName.innerText = data.userName;
        // displayEmail.innerText = data.userEmail;
        // displayUserBalance.innerText = data.userBalance;
        document.getElementById("displayUserId").innerText = data.userId;
        document.getElementById("displayUserName").innerText = data.userName;
        document.getElementById("displayEmail").innerText = data.userEmail;
        document.getElementById("displayUserBalance").innerText = data.userBalance;
        document.getElementById("displayUserStatus").innerText = data.status;

    });
}

function showWithdrawals(){
  document.getElementById("withdrawalsList").innerHTML = "";

  send("getWithdrawalsRequests").then(data => {
    if(!data || data.length==0){
      document.querySelector(".placeholder-text").innerHTML="There is no withdrawal requests"
      return
    }
    document.querySelector(".placeholder-text").innerHTML=""
    data.forEach(element => {
      renderWithdrawals(element.withdrawal_id, element.user_id, element.name,element.amount, element.created_at, element.description);
    });

  })
}

function renderWithdrawals(withdrawal_id,user_id, name, amount, date, description){
  const withdrawals = document.getElementById("withdrawalsList")
  document.getElementsByClassName("placeholder-text").innerText=""
  document.querySelector(".trans-text").classList.add("hidden")
  const newElement =document.createElement("div");
  newElement.className="transaction-item"
  newElement.innerHTML= `
          <p><strong>Withdrawal ID:</strong> ${withdrawal_id}</p>
          <p><strong>ID:</strong> ${name}-${user_id}</p>
          <p><strong>Date:</strong> ${date}</p>
          <p><strong>Amount:</strong> ${amount}</p> 
          <p><strong>Description:</strong> ${description}</p> 
          <div onclick= "handleRequest(${withdrawal_id})" style="cursor:pointer;width:fit-content; color:green">handle</div>`
  withdrawals.appendChild(newElement)
}


function showTransactions(){

  const displayElem = document.getElementById("displayUserId");
  const id = parseInt(displayElem.innerText || displayElem.textContent);

  if (isNaN(id)) {
      console.error("Could not find a valid User ID to fetch transactions");
      return;
  }
  send("getTransactions",{"selectedUserId":id}).then(data=>{
        if(!data || data.length==0){
          document.querySelector(".placeholder-text2").innerText="There is no transactions"
          return
        }
        document.querySelector(".placeholder-text2").innerHTML=" "

        document.getElementById("transactionsList").innerHTML=""
        data.forEach(element =>{
          renderUserTransactions(element.transfer_id, element.sender_name, element.receiver_name, element.sender_id,element.receiver_id,element.created_at, element.amount, element.Operation)
        });

  })
}
function renderUserTransactions(transfer_id,s_name,r_name,sender_id,receiver_id,date,amount,Operation){
  const transactions = document.getElementById("transactionsList")
  const newElement= document.createElement("div");
  document.querySelector(".trans-text").classList.remove("hidden")
  newElement.className="transaction-item"
  newElement.innerHTML=`
          <p><strong>Transaction ID:</strong> ${transfer_id}</p>
          <p><strong>Sender:</strong> ${s_name}-${sender_id}</p>
          <p><strong>Receiver:</strong> ${r_name}-${receiver_id}</p>
          <p><strong>Date:</strong> ${date}</p>
          <p><strong>Amount:</strong> ${amount}</p> 
          <p><strong>Type:</strong> ${Operation}</p> 
        `
        transactions.appendChild(newElement)
  };


function handleRequest(withdrawal_id){
  if(!withdrawal_id){
    alert("error")
    return
  }
  send("handleRequest", {withdrawal_id}).then(data =>{
    alert(data.comment)
  })

  };


function showUserActions(){
  document.getElementById("actionsContainer").classList.remove("hidden")
  document.getElementById("actionsPlaceholder").classList.add("hidden")
  document.getElementById("withdrawalsWrap").classList.add("hidden")
  document.getElementById("transactionsList").style.display= "block"
  let status = document.getElementById("displayUserStatus").innerText
  console.log(status)
  if(status==="suspended"){
        document.getElementById("suspendBtn").innerText="Unsuspend user"
  }
  else {
    document.getElementById("suspendBtn").innerText="Suspend user"
  }
  showTransactions()
  
}

function hideUserActions(){
  document.getElementById("actionsContainer").classList.add("hidden")
  document.getElementById("actionsPlaceholder").classList.remove("hidden")
}

function showDelete() {
    const userId = document.getElementById("displayUserId").innerText.trim();
    document.getElementById("deleteModal").classList.add("active");
    document.getElementById("deleteUserId").value = userId;
}


  
function showAddFunds(){
  const userId = document.getElementById("displayUserId").innerText.trim();
  document.getElementById("addModal").classList.add("active");
  document.getElementById("addUserId").value = userId;
}

function showSuspend(){
    const userId = document.getElementById("displayUserId").innerText.trim();
    document.getElementById("suspendModal").classList.add("active");
    document.getElementById("suspendUserId").value = userId;

}
//closing any open modal
function cancel() {
  document.querySelectorAll(".modal").forEach(modal => {
    modal.classList.remove("active");
  });
}

function deleteUser(){
  let userId = parseInt(document.getElementById("deleteUserId").value);
  if(!userId){
    alert("Please enter a valid user ID");
    return;
  }

  send("deleteUser", { userId })
    .then(data => {
      if(!data.success){
        alert(data.comment || "Deletion failed");
        cancel();
        return;
      }

      alert(data.comment);
      cancel();

      // Clear displayed user info
      document.getElementById("displayUserId").innerText = "";
      document.getElementById("displayUserName").innerText = "";
      document.getElementById("displayEmail").innerText = "";
      document.getElementById("displayUserBalance").innerText = "";

      hideUserActions();
    })
    .catch(err => {
      console.error(err);
      alert("An error occurred while deleting the user.");
    });
}

window.onload=function(){
    showWithdrawals();
    hideUserActions();
} 

function reloadData() {
    location.reload()
}



function deleteUser(){
  let userId = parseInt(document.getElementById("deleteUserId").value) ;
  let reason =document.getElementById("deleteReason").value;
  if(!reason){
    alert("Please provide a reason for deletion")
    return
  }

  send("deleteUser",{userId}).then(data =>{
    if(!data.success){
      alert(data.comment)
      document.getElementById("deleteReason").value =""
      cancel();
      return
    }
    alert(data.comment);
    cancel();
    document.getElementById("displayUserId").innerText = "";
    document.getElementById("displayUserName").innerText = "";
    document.getElementById("displayEmail").innerText = "";
    document.getElementById("displayUserBalance").innerText = "";
    //document.getElementById("deleteReason").value ="";
    // Hide user actions
    hideUserActions();
  }).catch(err => {
    console.error(err);
    alert("An error occurred while deleting the user.");
  });

  }


  function suspendUser(){
      let userId = parseInt(document.getElementById("suspendUserId").value) ;
      let reason =document.getElementById("suspendReason").value;
      let duration= document.getElementById("suspendDuration").value;
      if(!reason){
        alert("Please provide a reason for suspending")
        return
      }
      if(!duration){
        alert("Please provide a duration")
        return
      }
      send("suspendUser", {userId}).then(data =>{
        if(!data.success){
          alert(data.comment)
          document.getElementById("suspendReason").value =""
          cancel()
          return
        }
        alert(data.comment)
        cancel()
        document.getElementById("displayUserId").innerText = "";
        document.getElementById("displayUserName").innerText = "";
        document.getElementById("displayEmail").innerText = "";
        document.getElementById("displayUserBalance").innerText = "";
        hideUserActions();
        location.reload()
        }).catch(err => {
          console.error(err);
          alert("An error occurred while suspending the user.");
        });

}
