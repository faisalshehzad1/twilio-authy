<!--JS-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<script type="text/javascript">
	function check_approval(uuid) {
		$.post("<?php echo base_url('auth/check_approval_request')?>",{ id_data: uuid }, function( data ) {
			var post_json = JSON.parse(data);
			if(post_json.success == true){
			    console.log('>>>>'+post_json.approval_request.status);
				if(post_json.approval_request.status == "approved"){
					//console.log('>>>>>>>> approved');
					$.post("<?php echo base_url('auth/approval_update')?>",{ status: post_json.approval_request.status }, function(data){
                        window.location.href = "<?php echo base_url('/')?>";
					});
				}else if(post_json.approval_request.status == "denied"){
					console.log('no login >>>>'+ post_json.approval_request.status);
					window.location.href = "<?php echo base_url('/')?>";					
				}else{
					console.log(post_json.approval_request.status);
					setInterval(check_approval(uuid), 5000);
				}
			}else{
				$("#pleaseWaitDialog").modal("hide");
                alert('something went wrong!');
                return false;
			}			
		});
	}
    $('#one_touch').on('click', function (e) {
        e.preventDefault();
        var modalLoading = '<div class="modal" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false role="dialog">\
                <div class="modal-dialog">\
                    <div class="modal-content">\
                        <div class="modal-header">\
                            <h4 class="modal-title">Waiting for Authy Approval...</h4>\
                        </div>\
                        <div class="modal-body">\
                            <div class="progress">\
                              <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"\
                              aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; height: 40px">\
                              </div>\
                            </div>\
                        </div>\
                    </div>\
                </div>\
            </div>';
        $(document.body).append(modalLoading);
        $("#pleaseWaitDialog").modal("show");
        $.get("<?php echo base_url('auth/oneTouchLogin')?>", {dataType: 'json'}, function (data) {
            var json = JSON.parse(data);
            if (json.success == true) {
                var row_uuid = json.approval_request.uuid;
				var get_approval = setInterval(check_approval(row_uuid), 5000);
            }else {
                $("#pleaseWaitDialog").modal("hide");
                console.log('>>>> ERROR' + json);
                return false;
            }
        });
    });
</script>
</body>
</html>
