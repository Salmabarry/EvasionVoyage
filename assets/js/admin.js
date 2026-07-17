document.addEventListener("DOMContentLoaded", function () {
  if (window.lucide) lucide.createIcons();

  var toggle = document.getElementById("admin-sidebar-toggle");
  var sidebar = document.getElementById("admin-sidebar");
  if (toggle && sidebar) {
    toggle.addEventListener("click", function () {
      sidebar.classList.toggle("is-open");
    });
    document.addEventListener("click", function (e) {
      if (!sidebar.classList.contains("is-open")) return;
      if (sidebar.contains(e.target) || toggle.contains(e.target)) return;
      sidebar.classList.remove("is-open");
    });
  }
});
