<!doctype html>
<html>
<head>
    <title>
        <?php echo $page_title; ?>
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
    <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />
    <link rel="stylesheet" type="text/css" href="<?php echo SWB; ?>template/default/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo SWB; ?>template/default/assets/css/ddc.css" />
    <?php if (isset($css)) { echo $css; } ?>
        <script src="<?php echo SWB; ?>template/default/assets/js/jquery.min.js"></script>
        <script src="<?php echo SWB; ?>template/default/assets/js/bootstrap.min.js"></script>
        <script src="<?php echo SWB; ?>template/default/assets/js/dataTables.searchHighlight.min.js"></script>
        <script src="<?php echo SWB; ?>template/default/assets/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo SWB; ?>template/default/assets/js/dataTables.bootstrap.js"></script>
        <script src="<?php echo SWB; ?>template/default/assets/js/jquery.highlight.js"></script>
        <?php if (isset($js)) { echo $js; } ?>
</head>

<body id="top">
    <div id="pageContent">
        <a name="top"></a>
        <?php echo $content; ?>
            <div class="navbar-fixed-bottom" align="center">
                <a href="#top">
                    <button type="button" class="btn btn-primary">
                        <span class="glyphicon glyphicon-menu-up"></span>
                    </button>
                </a>
            </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="pop" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-body">
                    <?php
								$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='ddc_info'");
								while ($content = $ddc->fetch_row()) {
									echo"$content[0]";
								}
							?>
                </div>
            </div>

        </div>
    </div>
    <!-- block if we inside iframe -->
    <script type="text/javascript">
        $(document).ready(function () {
            $('#ddc').DataTable({
                searchHighlight: true
            });
        });
    </script>
    <script type="text/javascript">
        var _numberSearch = '';

        $.fn.dataTable.ext.search.push(function (settings, searchData) {
            if (!_numberSearch) {
                return true;
            }

            if (searchData[0].charAt(0) === _numberSearch) {
                return true;
            }

            return false;
        });


        $(document).ready(function () {
            var table = $('#ddc').DataTable();

            var number = $('<div class="number"/>').append('');

            $('<span class="clear active"/>')
                .data('letter', '')
                .html('All')
                .appendTo(number);

            for (var i = 18; i < 28; i++) {
                var letter = String.fromCharCode(30 + i);

                $('<span/>')
                    .data('letter', letter)
                    .html(letter)
                    .appendTo(number);
            }

            number.insertBefore(table.table().container());

            number.on('click', 'span', function () {
                number.find('.active').removeClass('active');
                $(this).addClass('active');

                _numberSearch = $(this).data('letter');
                table.draw();
            });
        });
    </script>
    <script type="text/javascript">
        $(function () {
            $('a[href*=#top]:not([href=#])').click(function () {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        $('html,body').animate({
                            scrollTop: target.offset().top
                        }, 1000);
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>