!function (s, n, i, t) {
  "use strict";
  void 0 === i.rocketshipUI && (i.rocketshipUI = {});
  var a = i.rocketshipUI;
  n.behaviors.rocketshipUIStatus = {
    attach: function (n, i) {
      var t = s(".messages--drupal", n);
      a.drupalMessages(t)
    }
  }, a.drupalMessages = function (n) {
    once("js-once-status", n).forEach((n) => {
      var n = s(n);
      n.find(".js-close").on("click", (function (s) {
        n.addClass("js-closing"), s.preventDefault()
      })), n.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", (function () {
        n.hasClass("js-closing") && n.addClass("js-closed")
      }))
    })
  }
}(jQuery, Drupal, window, document);
