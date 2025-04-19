            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Highlight active menu item
        $(document).ready(function() {
            var currentPage = window.location.pathname;
            $('.nav-link').each(function() {
                var href = $(this).attr('href');
                if (currentPage.endsWith(href)) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        });
    </script>
</body>
</html> 