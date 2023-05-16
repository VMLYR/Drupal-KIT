!function (a, e, s, l) {
  "use strict";
  void 0 === s.rocketshipUI && (s.rocketshipUI = {});
  var t = s.rocketshipUI;
  e.behaviors.rocketshipUIForms = {
    attach: function (e, s) {
      var l = a("input", e), o = a("select", e), i = a("textarea", e);
      (l.length || i.length) && t.focusFields(l, i, o), o.length && t.customSelect(o), i.length && t.textareaScroll(i), t.stateCheck()
    }
  }, t.focusFields = function (e, s, l) {
    e.length && once("js-once-input", e).forEach((e)=> {
      var e = a(e), s = ".form__element";
      e.closest(s).length < 1 && (s = ".form-wrapper"), e.focus((function (e) {
        var l = a(this);
        l.closest(s).addClass("is-active").find("label").addClass("is-active"), "search_block_form" == l.attr("name") && l.parent().addClass("is-active").find("label").addClass("is-active")
      })).blur((function (e) {
        var l = a(this);
        l.closest(s).removeClass("is-active").find("label").removeClass("is-active"), "search_block_form" == l.attr("name") && l.parent().removeClass("is-active").find("label").removeClass("is-active"), l.val() ? (l.closest(s).addClass("has-value").find("label").addClass("has-value"), "search_block_form" == l.attr("name") && l.parent().addClass("has-value").find("label").addClass("has-value")) : (l.closest(s).removeClass("has-value").find("label").removeClass("has-value"), "search_block_form" == l.attr("name") && l.parent().removeClass("has-value").find("label").removeClass("has-value"))
      })), e.val() && (e.closest(s).addClass("has-value").find("label").addClass("has-value"), "search_block_form" == e.attr("name") && e.parent().addClass("has-value").find("label").addClass("has-value"))
    }), s.length && once("js-once-textarea", s).forEach((s) => {
      var e = a(s), s = ".form__element";
      e.closest(s).length < 1 && (s = ".form-wrapper"), e.focus((function (e) {
        a(this).closest(s).addClass("is-active").find("label").addClass("is-active")
      })).blur((function (e) {
        var l = a(this);
        l.closest(s).removeClass("is-active").find("label").removeClass("is-active"), l.val() ? l.closest(s).addClass("has-value").find("label").addClass("has-value") : l.closest(s).removeClass("has-value").find("label").removeClass("has-value")
      })), e.val() && e.closest(s).addClass("has-value").find("label").addClass("has-value")
    }), l.length && once("js-once-select", l).forEach((l) => {
      var e = a(l), s = ".form__element";
      e.closest(s).length < 1 && (s = ".form-wrapper"), e.focus((function (e) {
        a(l).parent().parent().find("label").addClass("is-active")
      })).blur((function (e) {
        var l = a(l);
        l.closest(s).removeClass("is-active").find("label").removeClass("is-active"), l.val() ? l.closest(s).addClass("has-value").find("label").addClass("has-value") : l.closest(s).removeClass("has-value").find("label").removeClass("has-value")
      })), e.val() && (("object" == typeof e.val() && e.val().length) > 0 && e.closest(s).addClass("has-value").find("label").addClass("has-value"), "object" != typeof e.val() && e.closest(s).addClass("has-value").find("label").addClass("has-value"))
    })
  }, t.customSelect = function (e) {
    once("js-once-select-wrap", e).forEach((e) => {
      a(e).closest(".form__dropdown").length < 1 && a(e).wrap('<div class="form__dropdown"></div>')
    })
  }, t.textareaScroll = function (e) {
    once("js-once-textarea-scroll", e).forEach((e) => {
      var e = a(e), s = function (a) {
        a.scrollTop > 0 ? e.closest(".form__element").addClass("js-scrolling") : e.closest(".form__element").removeClass("js-scrolling")
      };
      s(e[0]), e.on("scroll", (function () {
        s(e)
      }))
    })
  }, t.stateCheck = function () {
    a(l).on("state:required", (function (e) {
      if (e.trigger && "function" == typeof a(e.target).isWebform && a(e.target).isWebform()) {
        var s = a(e.target).parent(".form-item").find(".form__label"), l = s.find(".form__label__required"),
          t = s.find(".form__label__not-required");
        e.value ? (void 0 !== l && l.removeAttr("aria-hidden"), void 0 !== t && t.attr("aria-hidden", !0)) : (void 0 !== t && t.removeAttr("aria-hidden"), void 0 !== l && l.attr("aria-hidden", !0))
      }
    }))
  }
}(jQuery, Drupal, window, document);
