var Utils = {
    init_spapp: () => {
        var app = $.spapp({
            templateDir: "./pages/",
            defaultView: "#home"
        });
        app.route({
            view: "home",
            load: "home.html"
        });
        app.route({
            view: "candidates",
            load: "candidates.html"
        });
        app.route({
            view: "vote",
            load: "vote.html"
        });
        app.route({
            view: "contact",
            load: "contact.html"
        });
        $(document).on("click", "a.nav-link", function () {
            setTimeout(()=>{
                window.scrollTo(0, 0);
            }, 1);
        });
        app.run();
    },
    datatable: function (table_id, columns, data, pageLength=15) {
        if ($.fn.dataTable.isDataTable("#" + table_id)) {
            $("#" + table_id)
            .DataTable()
            .destroy();
        }
        $("#" + table_id).DataTable({
          data: data,
          columns: columns,
          pageLength: pageLength,
          lengthMenu: [2, 5, 10, 15, 25, 50, 100, "All"],
        });
    },
    parseJwt: function(token) {
        if (!token) return null;
        try {
          const payload = token.split('.')[1];
          const decoded = atob(payload);
          return JSON.parse(decoded);
        } catch (e) {
          console.error("Invalid JWT token", e);
          return null;
        }
    }
};