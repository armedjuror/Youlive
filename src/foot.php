<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; <?=APP_NAME?> <?=date('Y')?></span>
        </div>
    </div>
</footer>
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<div class="modal fade" id="MessageModal" tabindex="-1" role="dialog" aria-labelledby="MessageModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content bg-gradient-default text-light">
            <div class="modal-body text-center">
                <div id="MessageModalIconContainer"></div><br>
                <div id="MessageModalTextContainer"></div><br>
                <div id="MessageModalButtonContainer"></div>
            </div>
        </div>
    </div>
</div>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-<?=THEME?>" href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="ui/extras/jquery/jquery.min.js"></script>
<script src="ui/extras/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="ui/extras/jquery-easing/jquery.easing.min.js"></script>
<script src="ui/extras/datatables/jquery.dataTables.min.js"></script>
<script src="ui/extras/datatables/dataTables.bootstrap4.min.js"></script>
<!-- Custom scripts for all pages-->
<script src="ui/js/sb-admin-2.min.js"></script>
<script>
    const IN_DEVELOPMENT = <?=$_ENV['DEV_MODE']?>;
    const THEME = '<?=THEME?>';

    $(document).ready(function() {
        const dataTable = $('#dataTable').DataTable();
    });
</script>
<script src="ui/js/utility.js"></script>
<script src="ui/js/ajax.js"></script>

</body>

</html>