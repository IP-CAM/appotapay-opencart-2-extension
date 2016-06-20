<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-pp-express" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a> </div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-pp-express" class="form-horizontal">
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab-api">
                            <div class="form-group required">
                                <label class="col-sm-2 control-label" for="entry-apikey"><?php echo $entry_apikey; ?></label>
                                <div class="col-sm-10">
                                    <input type="text" name="appota_payment_gateway_apikey" value="<?php echo $appota_payment_gateway_apikey; ?>" placeholder="<?php echo $entry_apikey; ?>" id="entry-apikey" class="form-control" />
                                    <?php if ($error_apikey) { ?>
                                    <div class="text-danger"><?php echo $error_apikey; ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group required">
                                <label class="col-sm-2 control-label" for="entry-apisecret"><?php echo $entry_apisecret; ?></label>
                                <div class="col-sm-10">
                                    <input type="text" name="appota_payment_gateway_apisecret" value="<?php echo $appota_payment_gateway_apisecret; ?>" placeholder="<?php echo $entry_apisecret; ?>" id="entry-apisecret" class="form-control" />
                                    <?php if ($error_apisecret) { ?>
                                    <div class="text-danger"><?php echo $error_apisecret; ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group required">
                                <label class="col-sm-2 control-label" for="entry-apiprivate"><?php echo $entry_apiprivate; ?></label>
                                <div class="col-sm-10">
                                    <input type="text" name="appota_payment_gateway_apiprivate" value="<?php echo $appota_payment_gateway_apiprivate; ?>" placeholder="<?php echo $entry_apiprivate; ?>" id="entry-apiprivate" class="form-control" />
                                    <?php if ($error_apiprivate) { ?>
                                    <div class="text-danger"><?php echo $error_apiprivate; ?></div>
                                    <?php } ?>
                                </div>
                            </div>    
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="appota_payment_gateway_status" id="input-status" class="form-control">
                                        <?php if ($appota_payment_gateway_status) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="appota_payment_gateway_order_status_id" id="input-order-status" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $appota_payment_gateway_order_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?> 