!function (e, t, o, n) {
  "use strict";
  void 0 === o.rocketshipUI && (o.rocketshipUI = {});
  var s = o.rocketshipUI;
  s.html = e("html"), s.body = e("body"), s.page = e("html, body"), s.touch = !1, s.screen = "", s.scrollStop = !1, t.behaviors.rocketshipUIHelpers = {
    attach: function (e, t) {
      s.checkScreenSize(), o.rocketshipUI.optimizedResize().add((function () {
        s.checkScreenSize()
      })), "undefined" != typeof Modernizr && Modernizr.addTest("flexboxtweener", Modernizr.testAllProps("flexAlign", "end", !0)), s.checkPassiveSupported()
    }
  }, s.checkPassiveSupported = function () {
    s.passiveSupported = !1;
    try {
      var e = {
        get passive() {
          s.passiveSupported = !0
        }
      };
      o.addEventListener("test", e, e), o.removeEventListener("test", e, e)
    } catch (e) {
      s.passiveSupported = !1
    }
  }, s.checkScreenSize = function () {
    var e = s.getBreakpoint();
    "bp-xs" == e && (s.screen = "xs"), "bp-sm" == e && (s.screen = "sm"), "bp-md" == e && (s.screen = "md"), "bp-lg" == e && (s.screen = "lg")
  }, s.getBreakpoint = function () {
    var e = o.getComputedStyle(n.body, "::after").getPropertyValue("content");
    return (e = e.replace(/"/g, "")).replace(/'/g, "")
  }, s.debounce = function (e, t, o) {
    var n;
    return function () {
      var s = this, i = arguments, r = function () {
        n = null, o || e.apply(s, i)
      }, l = o && !n;
      clearTimeout(n), n = setTimeout(r, t), l && e.apply(s, i)
    }
  }, s.optimizedResize = function () {
    var e = [], t = !1;

    function n() {
      t || (t = !0, o.requestAnimationFrame ? o.requestAnimationFrame(s) : setTimeout(s, 250))
    }

    function s() {
      e.forEach((function (e) {
        e()
      })), t = !1
    }

    return {
      add: function (t) {
        e.length || o.addEventListener("resize", n), function (t) {
          t && e.push(t)
        }(t)
      }
    }
  }, s.scrollTo = function (e) {
    e.pos = e.el.offset().top, void 0 === e.offset && (e.offset = 0), "bottom" === e.offset && (e.pos = e.el.offset().top + e.el.outerHeight()), void 0 === e.speed && (e.speed = 1e3), void 0 === e.callback && (e.callback = function () {
    }), s.page.on("scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove", (function (e) {
      s.scrollStop()
    })), s.page.stop().animate({scrollTop: e.pos + e.offset}, e.speed, (function () {
      e.callback(), s.page.off("scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove")
    }))
  }, s.scrollStop = function () {
    s.page.stop(!0, !1)
  }, s.getScrollTop = function () {
    return Modernizr.touch ? o.pageYOffset : e(o).scrollTop()
  }, s.imgLoaded = function (t, o) {
    var n = t.find("img"), s = n.length, i = 0;
    if (!s) return o();
    n.each((function () {
      var t = e(this);
      t.on("load", (function () {
        (i += 1) == s && o()
      })).each((function () {
        if (this.complete) {
          var n = t.attr("src");
          e(this).load(n), (i += 1) == s && o()
        }
      }))
    }))
  }, s.round = function (e, t) {
    return Number(Math.round(e + "e" + t) + "e-" + t)
  }
}(jQuery, Drupal, window, document), function (e, t, o, n) {
  "use strict";
  void 0 === o.rocketshipUI && (o.rocketshipUI = {});
  var s = o.rocketshipUI;
  s.body = document.body; // Add this line to assign document.body to s.body

  t.behaviors.rocketshipUIBase = {
    attach: function (t, o) {
      if (document.querySelector(".tabs__nav", t) !== null) {
        s.body.classList.add("has-tabs");
      }
      s.scrollToAnchor(0);

      var n = document.querySelector('a[href="#main-content"]');
      var i = document.querySelector("#main-content");

      if (n !== null && i !== null) {
        n.addEventListener("click", function (e) {
          i.focus();
          e.preventDefault();
        }, {once: true});
      }
    }
  }, s.cardLink = function (t, n, i) {
    var r = false;
    var elements = document.querySelectorAll(t);

    elements.forEach(function (element) {
      if (!element.classList.contains("js-once-cardlink")) {
        element.classList.add("js-once-cardlink");
        var t, l, a, c, u = element;
        if (n !== undefined && n.length) {
          t = u.querySelector(n);
          if (t === null || t.length < 1) {
            t = u.querySelector(i);
          }
          if (t === null || t.length < 1) {
            t = s.getCardLink(u).link;
          }
        }

        if (t !== null && t.length) {
          l = t.getAttribute("href");
          a = t.getAttribute("target");
          u.style.cursor = "pointer";

          u.addEventListener("mousedown", function (e) {
            c = +new Date();
            if (e.ctrlKey || e.metaKey) {
              r = true;
            }
          }, {once: true, capture: true});

          u.addEventListener("mouseup", function (event) {
            if (+new Date() - c < 250) {
              var n = event.currentTarget;
              if (!n.isEqualNode(event.target) && n.contains(event.target)) {
                return;
              }
              if (event.target.matches("a, a *")) {
                return;
              }
              if (r) {
                window.open(l, "_blank");
              } else if (a !== undefined) {
                window.open(l, a);
              } else {
                window.location.href = l;
              }
            }
            r = false;
          }, {once: true, capture: true});
        }
      }
    });
  }, s.getCardLink = function (e) {
    var t;
    return (t = e.find(".field--name-node-link a").first()).length || 1 === (t = e.find(".field--buttons a")).length || (t = e.find('[class*="field--name-field-link-"] a').last()).length ? {link: t} : {link: null}
  }, s.scrollToAnchor = function (t) {
    var i = !1, r = null;
    if ("undefined" != typeof drupalSettings && null !== drupalSettings && void 0 !== drupalSettings.theme_settings && null !== drupalSettings.theme_settings && (void 0 !== drupalSettings.theme_settings.scroll_to && null !== drupalSettings.theme_settings.scroll_to && drupalSettings.theme_settings.scroll_to && (i = !0), void 0 !== drupalSettings.theme_settings.scroll_to_exceptions && null !== drupalSettings.theme_settings.scroll_to_exceptions && drupalSettings.theme_settings.scroll_to_exceptions.length && (r = drupalSettings.theme_settings.scroll_to_exceptions)), i) {
      var l, a = e(".tabs"), c = 0, u = 0, h = e(".sticky-top");
      e("body").hasClass("toolbar-fixed") && (u = e("#toolbar-bar").outerHeight(), a.length && (c = a.outerHeight())), void 0 === t && (t = 0);
      var f = function () {
        return "fixed" === h.css("position") ? (t = h.outerHeight() + t + u + c, (l = h.outerHeight()) > t && (t = l)) : t = 15 + t + u + c, t = -t
      }, d = o.location.hash;
      if (e(d).length) {
        var p = e('a[href$="' + d + '"]');
        if (p.length < 1) {
          t = f();
          var g = {el: e(d), offset: t, speed: 1e3};
          s.scrollTo(g)
        }
        once("js-once-scrollable-anchors-active", p).forEach((p)=> {
          var o = e(p);
          if (null === r || !o.is(r)) {
            t = f();
            var n = {el: e(d), offset: t, speed: 1e3};
            s.scrollTo(n), o.addClass("js-active-anchor")
          }
        })
      }
      var v = document.querySelectorAll('a[href*="#"]:not(a[href="#"])');
      v.forEach(function (element) {
        if (!element.classList.contains("js-once-scrollable-anchors")) {
          element.classList.add("js-once-scrollable-anchors");
          element.addEventListener("click", function (event) {
            var i = event.currentTarget;
            if (r !== null && i.isEqualNode(r)) {
              return;
            }
            i.classList.remove("active");
            i.classList.remove("active-trail");

            if (event.target.href !== undefined) {
              a = event.target.href.split("#")[1];
            } else if (event.currentTarget.href !== undefined) {
              a = event.currentTarget.href.split("#")[1];
            }

            var u = document.querySelector("#" + a);
            var t = f();

            if (u !== null) {
              i.classList.add("js-active-anchor");
              var d = {
                el: u,
                offset: t,
                speed: 1000,
                callback: function () {
                  n.location.hash = c;
                }
              };
              s.scrollTo(d);
              if (o.history && o.history.pushState) {
                history.pushState("", n.title, c);
              }
            } else {
              var p = document.querySelectorAll('a[name="' + a + '"]');
              if (p.length) {
                i.classList.add("js-active-anchor");
                var g = {
                  el: p,
                  offset: t,
                  speed: 1000,
                  callback: function () {
                    n.location.hash = c;
                  }
                };
                s.scrollTo(g);
              }
            }

            var v = o.location.href.split(/[?#]/)[0];
            var m = h.split(/[?#]/)[0];
            if (v.replace(/\/$/, "") !== m.replace(/\/$/, "")) {
              return true;
            }
            event.preventDefault();
          }, {once: true});
        }
      }),
      o.onpopstate = function (n) {
        var elements = document.querySelectorAll('a[href*="#"]:not(a[href="#"])');
        elements.forEach(function (element) {
          var n = element;
          if (r === null || !n.isEqualNode(r)) {
            var i = n.href;
            var l = o.location.hash;
            var a = i.substring(i.indexOf("#"));
            var t = f();

            if (document.querySelector(a) !== null && l == a && (r === null || !n.isEqualNode(r))) {
              n.classList.add("js-active-anchor");
              var c = {
                el: document.querySelector(a),
                offset: t,
                speed: 1000
              };
              s.scrollTo(c);
            }
          }
        });
      }
    }
  }
}(jQuery, Drupal, window, document);
