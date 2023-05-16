!function (t, e, s, a) {
  "use strict";
  void 0 === s.rocketshipUI && (s.rocketshipUI = {});
  var n = s.rocketshipUI;
  e.behaviors.rocketshipUITables = {
    attach: function (e, s) {
      var a = t("table", e);
      a.length && n.setresponsiveTables(a)
    }
  }, n.setresponsiveTables = function (e) {
    once("js-once-set-responsive-tables", e).forEach((e) => {
      var e = t(e), s = e.closest(".page").length;
      s || (s = e.closest(".sb-show-main").length), s && (e.closest(".cke_show_borders, .text-long").length ? (n.tableSetAttributes(e), e.hasClass("table--reformatted") ? e.closest("div").hasClass("table-responsive") || e.wrap('<div class="table-responsive is-reformatted"></div>') : (e.closest("div").hasClass("table-responsive") || e.wrap('<div class="table-responsive has-scroll"></div>'), n.tableScroll(e, e.closest(".table-responsive")))) : e.closest(".table-responsive").hasClass("has-scroll") && n.tableScroll(e, e.closest(".table-responsive")))
    })
  }, n.tableScroll = function (e, a) {
    e.find("caption").insertBefore(a);
    var n = e.find("tr"), i = !1, o = !1, h = !1, l = !1, c = 0;
    if (once("js-once-check-th-position", n).forEach((n) => {
      t(n);
      var a = e.find("th");
      once("js-once-check-th-position", a).forEach((a) => {
        var s = t(n);
        0 === e && s.parent("tr").parent("tbody").length && (h = !0, l = !0), e === a.length - 1 && s.parent("tr").parent("tbody").length && (h = !0, !0), s.closest("thead").length && (i = !0, o = !0), s.closest("tfoot").length && (i = !0, !0)
      })
    }), i) a.addClass("js-table--th-row"), o ? a.addClass("js-table--th-top") : a.addClass("js-table--th-bottom"), d(e), s.rocketshipUI.optimizedResize().add((function () {
      d(e)
    })); else if (h) {
      a.addClass("js-table--th-col"), l ? a.addClass("js-table--th-left") : a.addClass("js-table--th-right");
      var r = e.find("th");
      r.each((function () {
        var e = t(this);
        e.html();
        e.wrapInner('<div class="th__content"></div>')
      })), f(r), s.rocketshipUI.optimizedResize().add((function () {
        f(r)
      }))
    } else a.addClass("js-table--no-th");

    function d(e) {
      e.find("th");
      for (var s = e.find("th, td"), a = 0; a < s.length; a++) {
        var n = s[a];
        t(n).css("height", "auto"), t(n).css("min-height", "0");
        var i = t(n).outerHeight();
        i > c && (c = i)
      }
      s.each((function () {
        t(this).css("min-height", c + "px")
      }))
    }

    function f(e) {
      e.each((function () {
        var e = t(this);
        e.find(".th__content").css("min-height", e.closest("tr").outerHeight() + "px")
      }))
    }
  }, n.tableSetAttributes = function (e) {
    var s = e.find("thead");
    if (s.length) {
      s.attr("tabindex", "0");
      var a = [];
      e.find("th").each((function () {
        a.push(t(this).text())
      }));
      var n = 0;
      e.find("tr").each((function () {
        n = 0, t(this).find("td").each((function () {
          t(this).attr("data-title", a[n]), ++n
        }))
      }))
    } else {
      var i = e.find("tbody");
      i.length && i.attr("tabindex", "0"), e.find("th").length ? e.find("tr").each((function () {
        var s = t(this).find("th").text();
        e.find("td").each((function () {
          t(this).attr("data-title", s)
        }))
      })) : e.addClass("no-th")
    }
  }
}(jQuery, Drupal, window, document);
