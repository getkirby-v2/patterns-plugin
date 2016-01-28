var menu = document.querySelector('.menu');

window.onunload = function() {
  localStorage.setItem('scrollPosition', menu.scrollTop);
};

if(localStorage.scrollPosition) {
  menu.scrollTop = localStorage.getItem('scrollPosition');
}
