function setCookie(name, value, daysToExpire) {
  const date = new Date();
  date.setTime(date.getTime() + (daysToExpire * 24 * 60 * 60 * 1000));
  const expires = "expires=" + date.toUTCString();
  document.cookie = name + "=" + value + "; " + expires + "; path=/";
}

function toggleSidebar() {
  document.getElementById('sidebar-menu').classList.toggle("show-sidebar-menu");
}

function confirmChoice(text, callback) {
  let confirmModal = $('<div>', { 'id': 'confirmModal', 'class': "modal fade bd-example-modal-sm", 'tabindex': "-1", 'role': "dialog", 'aria-labelledby': "confirmModal", 'aria-hidden': "true" }).append(
    $('<div>', { 'class': "modal-dialog modal-sm" }).append(
      $('<div>', { 'class': "modal-content" }).append(
        $('<div>', { 'class': "modal-header" }).append(
          $('<h4>', { 'class': "modal-title", 'id': "confirmModalLabel", 'text': 'Confirm' }),
          $('<button>', { 'type': "button", 'class': "btn-close", 'data-bs-dismiss': "modal", 'aria-label': "Close" })
        ),
        $('<div>', { 'class': "modal-body" }).append(
          text
        ),
        $('<div>', { 'class': "modal-footer" }).append(
          $('<button>', { 'type': "button", 'class': "btn btn-secondary shadow-secondary", 'data-bs-dismiss': "modal", 'text': 'Cancel' }),
          $('<button>', { 'type': "button", 'class': "btn btn-primary", 'data-bs-dismiss': "modal", 'text': 'Yes' }).on('click', function() {
            callback();
          })
        )
      )
    )
  );
  $(confirmModal).modal('show')
}

function popupMessage(type = 'info', message = "Missing default message", timeClose = "3000") {
  let element = {
    'info': {
      'background': '#78dbfb',
      'iconcolor': '#black',
      'tost': true
    },
    'error': {
      'background': 'red',
      'iconcolor': '#f94141',
      'tost': false
    },
    'warning': {
      'background': 'darkorange',//fb953d
      'iconcolor': '#f8bb86',
      'tost': false
    },
    'success': {
      'background': '#4caf50',
      'iconcolor': 'black',
      'tost': true
    }
  };

  const Toast = Swal.mixin({
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: timeClose,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  })

  Toast.fire({
    icon: type,
    title: '<p style="color:white;">' + message + '</p>',
    background: element[type].background,
    iconColor: element[type].iconColor,
  });
}

function setRefreshOnModalClose(){
  let btns = document.querySelectorAll("div.modal [data-bs-dismiss=modal]");
  btns.forEach(element => {
    element.dataset.refreshpage = "1";
  });
}

function refreshOnClose(el){
  if(el.dataset.refreshpage == '1') return location.reload()
  return;
}