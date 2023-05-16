!function (e, c, o, s) {
  "use strict";
  void 0 === o.rocketshipUI && (o.rocketshipUI = {});
  var r = o.rocketshipUI;
  c.behaviors.rocketshipUISearchBlock = {
    attach: function (c, o) {
      var s = e(".block--region-header-top.block--search-redirect-block"), n = s.find("h2");
      n.length && s.length && r.search(n, s)
    }
  }, r.search = function (e, c) {
    once("js-once-search", e).click((function (e) {
      c.hasClass("js-open") ? c.removeClass("js-open") : c.addClass("js-open"), e.preventDefault()
    }))
  }
}(jQuery, Drupal, window, document);
