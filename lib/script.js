document.addEventListener("DOMContentLoaded", () => {
  const createOrderForm = document.getElementById("createOrderForm");
  const editOrderForm = document.getElementById("editOrderForm");

  if (createOrderForm) {
    createOrderForm.addEventListener("submit", createOrder);
  }

  if (editOrderForm) {
    editOrderForm.addEventListener("submit", saveEditOrder);
  }
});

function sendSms(NumberOrder) {
  if (
    confirm(`Вы уверены, что хотите отправить СМС по заказу ${NumberOrder}?`)
  ) {
    $.ajax({
      url: "ajax_handler.php",
      type: "POST",
      data: {
        action: "send_sms",
        number: NumberOrder,
      },
      success: function (response) {
        alert("СМС отправлено");
        location.reload();
      },
      error: function (xhr, status, error) {
        console.error(xhr);
        alert("Произошла ошибка: " + error);
      },
    });
  }
}

function closeOrder(NumberOrder) {
  if (confirm(`Вы уверены, что хотите закрыть заказ ${NumberOrder}?`)) {
    $.ajax({
      url: "ajax_handler.php",
      type: "POST",
      data: {
        action: "close_order",
        number: NumberOrder,
      },
      success: function (response) {
        alert("Заказ закрыт");
        location.reload();
      },
      error: function (xhr, status, error) {
        console.error(xhr);
        alert("Произошла ошибка: " + error);
      },
    });
  }
}

document.addEventListener("DOMContentLoaded", function () {
  initStatusButtons();
  initRadioButtons();
});

function initStatusButtons() {
  document.querySelectorAll(".update-status").forEach((button) => {
    button.addEventListener("click", function () {
      updateOrderStatus(this);
    });
  });
}

/**
 * 
 * @param {HTMLElement} button
 */
function updateOrderStatus(button) {
  const orderNumber = button.dataset.order; 
  let status = parseInt(button.dataset.status); 


  if (status === 3) {
    button
      .querySelector("i")
      .classList.replace("fa-question-circle", "fa-exclamation-circle");
    status = 2; 
  } else if (status === 2) {
    button
      .querySelector("i")
      .classList.replace("fa-exclamation-circle", "fa-question-circle");
    status = 3; 
  }

  sendStatusUpdate(orderNumber, status, () => {
    button.dataset.status = status; 
    alert("Статус успешно обновлен.");
  });
}


function initRadioButtons() {
  document.querySelectorAll(".radio-button").forEach((button) => {
    button.addEventListener("click", function () {
      updateRadioStatus(this);
    });
  });
}

/**
 * 
 * @param {HTMLElement} button
 */
function updateRadioStatus(button) {
  const orderNumber = button.dataset.order;
  const action = button.value;

  sendStatusUpdate(orderNumber, action, () => {
    alert("Статус успешно обновлен.");
    location.reload();
  });
}

/**
 * 
 * @param {string} orderNumber 
 * @param {number|string} status 
 * @param {Function} callback 
 */
function sendStatusUpdate(orderNumber, status, callback) {
  fetch("ajax_handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=update_status&number=${orderNumber}&status=${status}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        callback();
      } else {
        alert("Ошибка: " + data.message);
      }
    })
    .catch((error) => console.error("Ошибка:", error));
}

function deleteOrder(NumberOrder) {
  if (confirm(`Вы уверены, что хотите удалить заказ ${NumberOrder}?`)) {
    $.ajax({
      url: "ajax_handler.php",
      type: "POST",
      data: {
        action: "delete_order",
        number: NumberOrder,
      },
      success: function (response) {
        alert("Заказ удалён");
        location.reload();
      },
      error: function (xhr, status, error) {
        console.error(xhr);
        alert("Произошла ошибка: " + error);
      },
    });
  }
}

function editOrder(NumberOrder, phone) {
  const editNumberOrder = document.getElementById("editNumberOrder");
  const editOrderPhone = document.getElementById("editOrderPhone");

  if (editNumberOrder && editOrderPhone) {
    editNumberOrder.value = NumberOrder;
    editOrderPhone.value = phone;
    let editOrderModal = new bootstrap.Modal(
      document.getElementById("editOrderModal")
    );
    editOrderModal.show();
  } else {
    console.error("Elements for editing order not found.");
  }
}

function createOrder(event) {
  event.preventDefault();
  const NumberOrder = document.getElementById("NumberOrder");
  const orderPhone = document.getElementById("orderPhone");

  if (NumberOrder && orderPhone) {
    $.ajax({
      url: "ajax_handler.php",
      type: "POST",
      data: {
        action: "create_order",
        number: NumberOrder.value,
        phone: orderPhone.value,
      },
      success: function (response) {
        alert(response.message);
        if (response.success) {
          location.reload();
        }
      },
      error: function (xhr, status, error) {
        console.error(xhr);
        alert("Произошла ошибка: " + error);
      },
    });
  } else {
    console.error("Elements for creating order not found.");
  }
}

function saveEditOrder(event) {
  event.preventDefault();
  const NumberOrder = document.getElementById("editNumberOrder");
  const orderPhone = document.getElementById("editOrderPhone");

  if (NumberOrder && orderPhone) {
    $.ajax({
      url: "ajax_handler.php",
      type: "POST",
      data: {
        action: "edit_order",
        number: NumberOrder.value,
        phone: orderPhone.value,
      },
      success: function (response) {
        alert(response.message);
        if (response.success) {
          location.reload();
        }
      },
      error: function (xhr, status, error) {
        console.error(xhr);
        alert("Произошла ошибка: " + error);
      },
    });
  } else {
    console.error("Elements for saving edited order not found.");
  }
}

function showCreateOrderModal() {
  let createOrderModal = new bootstrap.Modal(
    document.getElementById("createOrderModal")
  );
  createOrderModal.show();
}

document.addEventListener("DOMContentLoaded", function () {
  const phoneFields = document.querySelectorAll("#orderPhone, #editOrderPhone");

  phoneFields.forEach((field) => {
    field.addEventListener("input", function () {
      const phoneValue = field.value;

      if (phoneValue.startsWith("+7")) {
        if (phoneValue.length > 12) {
          field.value = phoneValue.slice(0, 12);
        }
      }
      else if (phoneValue.startsWith("8")) {
        if (phoneValue.length > 11) {
          field.value = phoneValue.slice(0, 11);
        }
      }
    });
  });
});

function clearCache() {
  if ("caches" in window) {
    caches.keys().then(function (cacheNames) {
      cacheNames.forEach(function (cacheName) {
        caches.delete(cacheName);
      });
    });
  }
}

document
  .getElementById("orderNumberFilter")
  .addEventListener("input", filterOrders);
document.getElementById("phoneFilter").addEventListener("input", filterOrders);
document
  .getElementById("statusFilter")
  .addEventListener("change", filterOrders);

function filterOrders() {
  const orderNumberValue = document
    .getElementById("orderNumberFilter")
    .value.toLowerCase();
  const phoneValue = document.getElementById("phoneFilter").value.toLowerCase();
  const statusValue = document
    .getElementById("statusFilter")
    .value.toLowerCase();
  const rows = document.querySelectorAll("#ordersTableBody tr");

  rows.forEach((row) => {
    const orderNumberText = row.cells[0].textContent.toLowerCase();
    const phoneText = row.cells[1].textContent.toLowerCase();
    const statusText = row.cells[2].textContent.toLowerCase();

    const orderNumberMatches = orderNumberText.includes(orderNumberValue);
    const phoneMatches = phoneText.includes(phoneValue);
    const statusMatches =
      statusValue === "" || statusText.includes(statusValue);

    if (orderNumberMatches && phoneMatches && statusMatches) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });
}

window.onload = clearCache;
