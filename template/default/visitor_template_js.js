


    function runScript(e){
        $('#rejectModalTap').on('shown.bs.modal', function () {
            setTimeout(function (){
                $('#activitiess').focus();
            }, 10);

        })

        if(e.keyCode == 13){

            var id = document.getElementById("memberid").value;
            $.ajax({
                url: "template/default/visitor_template_proses.php?id=" + id,
                data: {},
                dataType: "json",
                type: "get",
                success:function(data)
                {

                    var memberid = data[0]["memberid"];
                    var name = data[0]["name"];
                    var email = data[0]["email"];
                    $("#memberid2").html(memberid);
                    $("#memberid3").val(memberid);
                    $("#name").html(name);
                    $("#email").html(email);
                }
            });
            $('#rejectModalTap').modal();
        }
    }
