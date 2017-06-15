<?php
    $main_template_path = $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/login_template.inc.php';
?>

<script type="text/javascript" src="template/default/visitor_template_js.js"></script>
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        text-align: left;
        padding: 8px;
        font-family: Helvetica;
    }
    tr:nth-child(even){background-color: #f2f2f2}
</style>

<div class="s-visitor container">
    <header>
        <h1>Visitor Counter</h1>
        <div class="info">Please tap your RFID card</div>
        <div class="row">
        </div>
    </header>
    <form action="index.php?p=visitor" name="visitorCounterForm" id="visitorCounterForm" method="post" style="padding-top:50px;">
        <div class="row">
            <div class="col-lg-12 col-sm-12 col-xs-12">
                <input type="text" name="memberid" id="memberid" class="form-control input-lg" onkeypress="return runScript(event)"/>
                <label for="locker">Your ID card should writed in there</label>
            </div>
        </div>
    </form>
</div>


<div class="modal fade" id="rejectModalTap" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#1abc9c">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" style="color:white">Visitor Detail</h4>
            </div>
	        <div class="modal-body" style="color:black">
                <div class="row" style="margin:10px;">
                    <table>
                          <tr>
                            <th style="background-color: #1abc9c;color: white;width:30%;border-bottom: 0.5px solid #1ae0b9;"><h5>Member ID</h5></th>
                            <th><h5 id="memberid2">105216053</h5></th>
                          </tr>
                          <tr>
                            <th style="background-color: #1abc9c;color: white;border-bottom: 1px solid #1ae0b9;"><h5>Nama</h5></th>
                            <th><h5 id="name">Megandi</h5></th>
                          </tr>
                          <tr>
                            <th style="background-color: #1abc9c;color: white;border-bottom: 1px solid #1ae0b9;"><h5>Member's Email</h5></th>
                            <th style="border-bottom: 1px solid #ddd;"><h5 id="email">Standart</h5></th>
                          </tr>
                          <tr>
                            <th style="background-color: #1abc9c;color: white;border-bottom: 1px solid #1ae0b9;"><h5>Number Locker</h5></th>
                            <th style="border-bottom: 1px solid #ddd;"><input type="text" class="form-control input-md" onkeypress="return runScript(event)"/></th>
                          </tr>
                    </table>
                </div>
	        </div>
            <div class="modal-footer">
                <button id="submit" class="btn btn-primary">Save Locker</button>
            </div>
        </div>
    </div>
</div>
