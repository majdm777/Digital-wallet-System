
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

        displayUserId.innerText = data.userId;
        displayUserName.innerText = data.userName;
        displayEmail.innerText = data.userEmail;
        displayUserBalance.innerText = data.userBalance;
    });
}












































































































































// let currentUser = null
// let showingWithdrawals=true;
// let selectedTransaction = null
// let withdrawalsData = [];
// let userTransactions = [];


// // DOM Elements
// const searchBtn = document.getElementById("searchBtn")
// const userIdSearch = document.getElementById("userIdSearch")
// const userInfo = document.getElementById("userInfo")
// const displayUserId = document.getElementById("displayUserId")
// const displayUserName = document.getElementById("displayUserName")
// const displayUserBalance = document.getElementById("displayUserBalance")
// const transactionsList = document.getElementById("transactionsList")
// const transactionSearch = document.getElementById("transactionSearch")
// const actionsContainer = document.getElementById("actionsContainer")
// const actionsPlaceholder = document.getElementById("actionsPlaceholder")

// // Modal Elements
// const deleteModal = document.getElementById("deleteModal")
// const suspendModal = document.getElementById("suspendModal")
// const addModal = document.getElementById("addModal")

// // Button Elements
// const deleteBtn = document.getElementById("deleteBtn")
// const suspendBtn = document.getElementById("suspendBtn")
// const addBtn = document.getElementById("addBtn")

// // Delete Modal
// const deleteUserId = document.getElementById("deleteUserId")
// const deleteReason = document.getElementById("deleteReason")
// const deleteCancelBtn = document.getElementById("deleteCancelBtn")
// const deleteConfirmBtn = document.getElementById("deleteConfirmBtn")

// // Suspend Modal
// const suspendUserId = document.getElementById("suspendUserId")
// const suspendReason = document.getElementById("suspendReason")
// const suspendDuration = document.getElementById("suspendDuration")
// const suspendCancelBtn = document.getElementById("suspendCancelBtn")
// const suspendConfirmBtn = document.getElementById("suspendConfirmBtn")

// // Add Modal
// const addUserId = document.getElementById("addUserId")
// const addAmount = document.getElementById("addAmount")
// const addCancelBtn = document.getElementById("addCancelBtn")
// const addConfirmBtn = document.getElementById("addConfirmBtn")

// if (searchBtn) {
//   searchBtn.addEventListener("click", handleSearch)
// }
// if (userIdSearch) {
//   userIdSearch.addEventListener("keypress", (e) => {
//     if (e.key === "Enter") {
//       handleSearch()
//     }
//   })
// }

// // Add click handler to user info box
// if (userInfo) {
//   userInfo.addEventListener("click", () => {
//     if (currentUser) {
//       toggleView();
//     }
//   })
// }

// // Add transaction search event listener
// if (transactionSearch) {
//   transactionSearch.addEventListener("input", (e) => {
//     const searchTerm = e.target.value.toLowerCase()
//     filterTransactions(searchTerm)
//   })
// }
// //using the fetch api to send requests to admin.php, it adds the action parameter to the url
// //action=search
// async function handleSearch() {


//   const userId = userIdSearch.value.trim().toUpperCase()
//       if (currentUser && currentUser.id === userId) {
//       resetDisplay();
//       return;
//     }
//   if (!userId) {
//     alert("Please enter a User ID")
//     return
//   }

//   try {
//     const response = await fetch(`admin.php?action=search&userId=${encodeURIComponent(userId)}`) //action search

//     if (!response.ok) { 
//       const errorData = await response.json().catch(() => ({}));
//       throw new Error(errorData.message || `HTTP error! status: ${response.status}`)
//     }

//     const data = await response.json()// response ok, so we get the data and convert it to jason format

//     if (data.success) {
//       currentUser = data.user
//       displayUserInfo(data.user)
//       displayUserTransactions(data.transactions)
//       hideActions()
//       showingWithdrawals=false;
//     } else {
//       alert(data.message || "User not found!")
//       resetDisplay()
//     }
//   } catch (error) {
//     console.error("Error searching user:", error)
//     alert("An error occurred while searching for the user. Please check the console for details.")
//   }
// }

// function displayUserInfo(user) {
//   displayUserId.textContent = user.id
//   displayUserName.textContent = user.name
//   displayUserBalance.textContent = Number.parseFloat(user.balance).toFixed(2)
//   userInfo.classList.remove("hidden")
// }

// function displayUserTransactions(transactions) {
//   userTransactions = transactions; // store separately
//   transactionSearch.value = "";
//   renderUserTransactions(transactions); 
// }

// function renderUserTransactions(transactions) {
//   transactionsList.innerHTML = "";
//   if(transactions.length === 0){
//     transactionsList.innerHTML = '<p class="placeholder-text">No transactions found</p>';
//     return;
//   }
//   transactions.forEach(t => {
//     const div = document.createElement("div");
//     div.className = "transaction-item";
//     div.innerHTML = `
//       <p><strong>ID:</strong> ${t.id}</p>
//       <p><strong>Date:</strong> ${t.date}</p>
//       <p><strong>Description:</strong> ${t.description}</p>
//       <p class="transaction-amount ${t.type === "credit" ? "amount-positive" : "amount-negative"}">
//         <strong>Amount:</strong> ${t.type === "credit" ? "+" : ""}$${Number.parseFloat(t.amount).toFixed(2)}
//       </p>
//     `;
//     transactionsList.appendChild(div);
//   });
// }


// function filterTransactions(searchTerm) {
//   if (!searchTerm) {
//     renderUserTransactions(userTransactions)
//     return
//   }

//   const filtered = userTransactions.filter((transaction) => {
//     const id = transaction.id.toLowerCase()
//     const description = transaction.description.toLowerCase()
//     const date = transaction.date.toLowerCase()
//     const amount = transaction.amount.toString()

//     return (
//       id.includes(searchTerm) ||
//       description.includes(searchTerm) ||
//       date.includes(searchTerm) ||
//       amount.includes(searchTerm)
//     )
//   })

//   renderUserTransactions(filtered)
// }

// function showActions() {
//   actionsContainer.classList.remove("hidden")
//   actionsPlaceholder.classList.add("hidden")
// }

// function hideActions() {
//   actionsContainer.classList.add("hidden")
//   actionsPlaceholder.classList.remove("hidden")
// }

// function resetDisplay() {
//   currentUser = null
//   selectedTransaction = null
//   userInfo.classList.add("hidden")
//   hideActions() 
//   renderWithdrawals(withdrawalsData)
//   showingWithdrawals=true;
// }

// deleteBtn.addEventListener("click", () => {
//   if (currentUser) {
//     deleteUserId.value = currentUser.id
//     deleteReason.value = ""
//     deleteModal.classList.add("active")
//   }
// })

// deleteCancelBtn.addEventListener("click", () => {
//   deleteModal.classList.remove("active")
// })

// //This is used when the admin confirms a user deletion.
// // It's in the event listener for the deleteConfirmBtn.
// deleteConfirmBtn.addEventListener("click", async () => {
//   const reason = deleteReason.value.trim()

//   if (!reason) {
//     alert("Please provide a reason for deletion")
//     return
//   }

//   try {
//     const response = await fetch("admin.php?action=delete", {
//       method: "POST",
//       headers: {
//         "Content-Type": "application/json",
//       },
//       body: JSON.stringify({
//         userId: currentUser.id,
//         reason: reason,
//       }),
//     })

//     const data = await response.json()

//     if (data.success) {
//       alert(`User ${currentUser.id} has been deleted.\nReason: ${reason}`)
//       deleteModal.classList.remove("active")
//       resetDisplay()
//       userIdSearch.value = ""
//     } else {
//       alert(data.message || "Failed to delete user")
//     }
//   } catch (error) {
//     console.error("Error deleting user:", error)
//     alert("An error occurred while deleting the user")
//   }
// })

// suspendBtn.addEventListener("click", () => {
//   if (currentUser) {
//     suspendUserId.value = currentUser.id
//     suspendReason.value = ""
//     suspendDuration.value = ""
//     suspendModal.classList.add("active")
//   }
// })

// suspendCancelBtn.addEventListener("click", () => {
//   suspendModal.classList.remove("active")
// })

// //This is used when the admin confirms a user suspension.
// // It's in the event listener for the suspendConfirmBtn.
// suspendConfirmBtn.addEventListener("click", async () => {
//   const reason = suspendReason.value.trim()
//   const duration = suspendDuration.value.trim()

//   if (!reason) {
//     alert("Please provide a reason for suspension")
//     return
//   }

//   if (!duration) {
//     alert("Please provide suspension duration")
//     return
//   }

//   try {
//     const response = await fetch("admin.php?action=suspend", {
//       method: "POST",
//       headers: {
//         "Content-Type": "application/json",
//       },
//       body: JSON.stringify({
//         userId: currentUser.id,
//         reason: reason,
//         duration: duration,
//       }),
//     })

//     const data = await response.json()

//     if (data.success) {
//       alert(`User ${currentUser.id} has been suspended.\nReason: ${reason}\nDuration: ${duration}`)
//       suspendModal.classList.remove("active")
//     } else {
//       alert(data.message || "Failed to suspend user")
//     }
//   } catch (error) {
//     console.error("Error suspending user:", error)
//     alert("An error occurred while suspending the user")
//   }
// })

// addBtn.addEventListener("click", () => {
//   if (currentUser) {
//     addUserId.value = currentUser.id
//     addAmount.value = ""
//     addModal.classList.add("active")
//   }
// })

// addCancelBtn.addEventListener("click", () => {
//   addModal.classList.remove("active")
// })

// //This is used when the admin adds funds to a user's account.
// //  It's in the event listener for the addConfirmBtn
// addConfirmBtn.addEventListener("click", async () => {
//   const amount = Number.parseFloat(addAmount.value)

//   if (!amount || amount <= 0) {
//     alert("Please enter a valid amount")
//     return
//   }

//   try {
//     const response = await fetch("admin.php?action=addFunds", {
//       method: "POST",
//       headers: {
//         "Content-Type": "application/json",
//       },
//       body: JSON.stringify({
//         userId: currentUser.id,
//         amount: amount,
//       }),
//     })

//     const data = await response.json()

//     if (data.success) {
//       // Update displayed balance
//       currentUser.balance = data.newBalance
//       displayUserBalance.textContent = Number.parseFloat(data.newBalance).toFixed(2)

//       alert(
//         `$${amount.toFixed(2)} has been added to user ${currentUser.id}'s account.\nNew balance: $${Number.parseFloat(data.newBalance).toFixed(2)}`,
//       )
//       addModal.classList.remove("active")

//       // Refresh transactions to show the new transaction
//       handleSearch()
//     } else {
//       alert(data.message || "Failed to add funds")
//     }
//   } catch (error) {
//     console.error("Error adding funds:", error)
//     alert("An error occurred while adding funds")
//   }
// })

// // Close modals when clicking outside
// window.addEventListener("click", (e) => {
//   if (e.target === deleteModal) {
//     deleteModal.classList.remove("active")
//   }
//   if (e.target === suspendModal) {
//     suspendModal.classList.remove("active")
//   }
//   if (e.target === addModal) {
//     addModal.classList.remove("active")
//   }
// })

// //load withdrawals initially
// window.addEventListener("DOMContentLoaded", loadWithdrawals);
// //function to load all pending withdrawals

// async function loadWithdrawals(){
//   try{
//     const response= await fetch("admin.php?action=fetchWithdrawals");
//     const data= await response.json();
//     if(data.success){
//       withdrawalsData = data.withdrawals;
//       renderWithdrawals(withdrawalsData);

//       showingWithdrawals=true;
//       hideActions();
//       userInfo.classList.add("hidden");
//     }else{
//       transactionsList.innerHTML='<p class="placeholder-text">${data.message} || "no withdrawals found"</p>';

//     }


//   }catch(error){
//     console.error("Error loading withdrawals: ", error);
//     transactionsList.innerHTML='<p class="placeholder-text">Failed to load withdrawals.</p>'
//   }
// }


// //Toggle between withdrawals and user info

// function toggleView(){
//   if(showingWithdrawals){
//     displayUserInfo(currentUser);
//     displayUserTransactions(userTransactions);
//     showActions();
//     showingWithdrawals= false;
//   }else{
//     loadWithdrawals();
//     showingWithdrawals=true;
//     hideActions();
//     userInfo.classList.add("hidden");

  
//   }
// }

// //withdrawals renderer

// function renderWithdrawals(withdrawals) {
//   transactionsList.innerHTML = "";

//   withdrawals.forEach(w => {
//     const div = document.createElement("div");
//     div.className = "withdrawal-item";

//     div.innerHTML = `
//       <p><strong>User:</strong> ${w.name} (${w.user_id})</p>
//       <p class="withdrawal-amount">$${w.amount}</p>
//       <p>${w.description ?? "No description"}</p>
//       <p><small>${w.created_at}</small></p>
//     `;

//     transactionsList.appendChild(div);
//   });
// }
