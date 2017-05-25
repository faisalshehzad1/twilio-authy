<div class="container" id="main">
    <div id="signupbox" style="margin-top:80px" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Verification code</div>
            </div>
            <div class="panel-body">
                <?php if(!empty(@$notif['message'])){ ?>
                    <div class="alert alert-<?php echo @$notif['type'];?> alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                            <p><?php echo $notif['message'];?></p>
                            <span></span>

                    </div>
                <?php } ?>
                <?php if(!empty($this->session->flashdata('message')['error_message'])){ ?>
                    <div class="alert alert-<?php echo $this->session->flashdata('message')['type'];?> alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <?php foreach ($this->session->flashdata('message')['error_message'] as $row){?>
                            <p><?php echo $row;?></p>
                            <span></span>
                        <?php } ?>
                    </div>
                <?php } ?>
                <h4>Authy Token</h4>
                <p>You can get this token from the Authy mobile app.</p>
                <?php echo form_open('', array('class' => 'form-horizontal', 'role' => 'form')); ?>
                <div class="form-group">
                    <label for="" class="col-md-3 control-label">Token</label>
                    <div class="col-md-9">
                        <?php echo form_input('sms_code', '', array('class' => 'form-control')); ?>
                    </div>
                </div>

                <div style="border-top: 1px solid #999; padding-top:20px" class="form-group">
                    <div class="col-md-offset-3 col-md-9">
                        <input type="hidden" name="save" value="yes">
                        <input type="submit" class="btn btn-primary" value=" &nbsp Submit &nbsp"> or <input type="button"  id="one_touch" class="btn btn-danger" value=" via One Touch "><br><br>
                        <p>I don't have the app. <a href="<?php echo base_url('/auth/request_token')?>" class="text-info bold">Request token via SMS on  <?php echo $notif['cell_phone']?></a></p>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>

    </div>
</div>

