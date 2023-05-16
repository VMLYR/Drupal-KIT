!function (n, e, a, o) {
  "use strict";
  void 0 === a.rocketshipUI && (a.rocketshipUI = {});
  var t = a.rocketshipUI;
  e.behaviors.rocketshipUIDropdown = {
    attach: function (e, a) {
      n(".wrapper--navigation", e);
      var o = n(".nav--main, .nav--secondary", e);
      if (o.length) {
        var s = {nav: o, interaction: "hover"};
        t.dropdownMenu(s)
      }
    }
  }, t.dropdownMenu = function (e) {
    once("js-once-dropdown", e.nav).forEach((nav) => {
      var s = n(nav), r = e.interaction, i = s.children("ul").children("li");
      once("js-once-dropdown-item", i).forEach((i) => {
        var i, c, u = n(i), h = u.children("a, span").first(), v = u.find(".expand-sub");
        u.hasClass("has-sub") && ((i = void 0 !== v && v.length ? v : u) === u && "click" != r || (u.on("touchstart", (function () {
          t.touch = !0
        })), u.on("touchend", (function () {
          setTimeout((function () {
            t.touch = !1
          }), 500)
        }))), h.is("span") ? u.on("touchstart", (function (n) {
          t.touch = !0, (c = e.megamenu && "xs" !== t.screen && "sm" !== t.screen ? s : u).hasClass("js-open") ? t.navPrimaryClose(c) : t.navPrimaryOpen(c)
        })) : i.on("touchstart", (function (n) {
          t.touch = !0, (c = e.megamenu && "xs" !== t.screen && "sm" !== t.screen ? s : u).hasClass("js-open") ? t.navPrimaryClose(c) : t.navPrimaryOpen(c), n.preventDefault()
        })), i.on("touchend", (function (n) {
          setTimeout((function () {
            t.touch = !1
          }), 500), n.preventDefault()
        })), "hover" == r ? (u.on("mouseenter", (function (n) {
          c = u, t.touch || (t.navPrimaryOpen(c), n.preventDefault())
        })), u.on("mouseleave", (function (n) {
          c = u, t.navPrimaryClose(c), n.preventDefault()
        }))) : (h.is("span") ? u.on("click", (function (n) {
          "xs" !== t.screen && "sm" !== t.screen || (c = u, t.touch || (c.hasClass("js-open") ? t.navPrimaryClose(c) : t.navPrimaryOpen(c), n.preventDefault()))
        })) : i.on("click", (function (n) {
          "xs" !== t.screen && "sm" !== t.screen || (c = u, t.touch || (c.hasClass("js-open") ? t.navPrimaryClose(c) : t.navPrimaryOpen(c), n.preventDefault()))
        })), n(o).mouseup((function (n) {
          c = u, u.hasClass("expanded") && c.hasClass("js-open") && (u.is(n.target) || 0 !== u.has(n.target).length || t.navPrimaryClose(c))
        }))))
      });
      a.addEventListener("resize", (function (e) {
        i.each((function () {
          var e = n(this);
          t.navPrimaryClose(e)
        }))
      }), !1)
    })
  }, t.navHeight = function (n) {
    for (var e = n.find("li.expanded"), a = 0, o = 0; o < e.length; ++o) {
      var t = e.eq(o).find("ul").outerHeight(!0);
      t > a && (a += t)
    }
    return a
  }, t.navPrimaryOpen = function (n) {
    n.addClass("js-open")
  }, t.navPrimaryClose = function (n) {
    n.removeClass("js-open")
  }
}(jQuery, Drupal, window, document);
