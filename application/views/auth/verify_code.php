<div class="container" id="main">
    <div id="signupbox" style="margin-top:80px" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Authentication Code</div>
            </div>
            <div class="panel-body">
                <?php if(!empty(@$notif['message'])){ ?>
                    <div class="alert alert-<?php echo @$notif['type'];?> alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <?php foreach ($notif['message'] as $row){?>
                            <p><?php echo $row;?></p>
                            <span></span>
                        <?php } ?>
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
                <h4>We need to verify you're a human</h4>
                <p>Please enter the verification code we sent to your phone. If you didn't receive a code, you can <a href="<?php echo base_url('auth/two_factor');?>">try again</a></p>
                <?php echo form_open('', array('class' => 'form-horizontal', 'role' => 'form')); ?>
                <div class="form-group">
                    <label for="" class="col-md-3 control-label">SMS Code</label>
                    <div class="col-md-9">
                        <?php echo form_input('sms_code', '', array('class' => 'form-control')); ?>
                    </div>
                </div>

                <div style="border-top: 1px solid #999; padding-top:20px" class="form-group">
                    <div class="col-md-offset-3 col-md-9">
                        <input type="hidden" name="save" value="yes">
                        <input type="submit" class="btn btn-primary" value=" &nbsp Submit &nbsp">
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>

    </div>
</div>


