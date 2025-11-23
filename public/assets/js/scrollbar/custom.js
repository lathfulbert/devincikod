(function () {
  // Wait for DOM to be loaded before initializing SimpleBar
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSimpleBar);
  } else {
    initSimpleBar();
  }

  function initSimpleBar() {
    var myElement = document.getElementById("simple-bar");
    if (myElement && typeof SimpleBar !== 'undefined') {
      new SimpleBar(myElement, { autoHide: true });
    }
  }
})();
