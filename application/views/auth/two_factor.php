<link href="//cdnjs.cloudflare.com/ajax/libs/authy-form-helpers/2.3/form.authy.min.css" media="screen" rel="stylesheet"
      type="text/css">
<script src="//cdnjs.cloudflare.com/ajax/libs/authy-form-helpers/2.3/form.authy.min.js" type="text/javascript"></script>
<div class="container" id="main">
    <div id="signupbox" style="margin-top:80px" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Enable Two-Factor Authentication</div>
            </div>
            <div class="panel-body">
                <p>We need to verify you're a human.<br>
                    We will send a verification code via SMS to number below<br>
                    The phone number you provide will be used for authentication when you login to this app.</p>
                <?php echo form_open('', array('class' => 'form-horizontal', 'role' => 'form')); ?>
                <div class="form-group">
                    <label for="" class="col-md-3 control-label">Country</label>
                    <div class="col-md-9">
                        <select id="authy-countries" name="country-code" class="form-control"></select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="" class="col-md-3 control-label">Cellphone</label>
                    <div class="col-md-9">
                        <input id="authy-cellphone" type="text" value="" name="authy-cellphone" class="form-control" />
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


