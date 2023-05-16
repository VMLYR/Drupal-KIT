(function (e, n, o, s) {
  "use strict";
  void 0 === o.rocketshipUI && (o.rocketshipUI = {});
  var i = o.rocketshipUI;
  n.behaviors.rocketshipUIMobileMenu = {
    attach: function (n, o) {
      var s = e(".wrapper--navigation", n), a = s.find(".navigation__toggle-expand", n);
      a.length && s.length && i.mobileMenu(a, s)
    }
  }, i.mobileMenu = function (n, s) {
    var clickHandler = function (e) {
      "xs" == i.screen && (s.classList.contains("js-open") ? s.classList.remove("js-open") : s.classList.add("js-open")), e.preventDefault()
    };

    var once = function (element, event, handler) {
      var wrappedHandler = function () {
        handler.apply(this, arguments);
        element.removeEventListener(event, wrappedHandler);
      };

      element.addEventListener(event, wrappedHandler);
    };

    once(n[0], 'click', clickHandler);

    e(o).on("resize", function () {
      "xs" != i.screen && s.classList.contains("js-open") && s.classList.remove("js-open")
    });
  }
})(jQuery, Drupal, window, document);
