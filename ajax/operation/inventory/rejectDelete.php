<?php 
$general->createLog('stock_entry_deleted',$_POST);
$id = intval($_POST['id']);
$stock_in_out_type=$_POST['stock_in_out_type']=='stock_entry'?'stock_entry':'reject_entry';
if($stock_in_out_type=='stock_entry'){
    $rejectProductDelete= stock_entry_product_delete($db, $acc, $id);
}
else{
    $rejectProductDelete= rejectProductDelete($db, $acc, $id);
}

if($rejectProductDelete['status']==1){
    $jArray['status']=1;
    setMessage(2,$rejectProductDelete['message']);
}
else{
    $jArray['status']=0;
    setMessage(3,$rejectProductDelete['message']);
}

function rejectProductDelete(DB $db,ACC $acc,$id) {
    // Validate and retrieve reject_product details
    $reject_product = $db->get_rowData('reject_products', 'id', intval($id));
    if (empty($reject_product)) {
        setMessage(63, 'reject product');
        return ['status' => 0, 'message' => 'Reject product not found'];
    }

    // Start transaction
    $db->transactionStart();

    // Fetch voucher details
    $voucher = $acc->voucherDetails(V_T_PRODUCT_REJECT, $id);
    if (empty($voucher)) {
        return ['status' => 0, 'message' => 'Voucher not found'];
    }
    $voucher = $voucher[array_key_first($voucher)];

    // Fetch product details
    $products = $db->get_rowData('products', 'id', $reject_product['product_id']);
    if (empty($products)) {
        return ['status' => 0, 'message' => 'Product not found'];
    }

    // Calculate next stock quantity
    $nextQty = $products['stock'] + $reject_product['quantity'];

    // Update product stock
    $update = $db->update('products', ['stock' => $nextQty], ['id' => $reject_product['product_id']]);
    if ($update === false) {
        return ['status' => 0, 'message' => 'Failed to update product stock'];
    }

    // Delete reject_product entry
    $delete = $db->delete('reject_products', ['id' => $id]);
    if ($delete === false) {
        return ['status' => 0, 'message' => 'Failed to delete reject product'];
    }

    // Delete voucher
    $deleteVoucher = $acc->voucher_delete($voucher['id']);
    if ($deleteVoucher === false) {
        return ['status' => 0, 'message' => 'Failed to delete voucher'];
    }

    // Delete product stock change log
    $deleteLog = $db->delete('product_stock_log', ['reference_id' => $id, 'change_type' => ST_CH_REJECT]);
    if ($deleteLog === false) {
        return ['status' => 0, 'message' => 'Failed to delete stock change log'];
    }

    // Commit the transaction if all steps were successful
    $db->transactionStop(true);
    // Return success
    return ['status' => 1, 'message' => 'Reject product successfully deleted'];
}

function stock_entry_product_delete(DB $db,ACC $acc,$id) {
    // Validate and retrieve reject_product details
    $reject_product = $db->get_rowData('products_stock_in', 'id', intval($id));
    if (empty($reject_product)) {
        setMessage(63, 'stock entry product');
        return ['status' => 0, 'message' => 'stock entry data not found'];
    }

    // Start transaction
    $db->transactionStart();

    // Fetch voucher details
    $voucher = $acc->voucherDetails(V_T_PRODUCT_STOCK_ENTRY, $id);
    if (empty($voucher)) {
        return ['status' => 0, 'message' => 'Voucher not found'];
    }
    $voucher = $voucher[array_key_first($voucher)];

    // Fetch product details
    $products = $db->get_rowData('products', 'id', $reject_product['product_id']);
    if (empty($products)) {
        return ['status' => 0, 'message' => 'Product not found'];
    }

    // Calculate next stock quantity
    $nextQty = $products['stock'] - $reject_product['quantity'];

    // Update product stock
    $update = $db->update('products', ['stock' => $nextQty], ['id' => $reject_product['product_id']]);
    if ($update === false) {
        return ['status' => 0, 'message' => 'Failed to update product stock'];
    }

    // Delete reject_product entry
    $delete = $db->delete('products_stock_in', ['id' => $id]);
    if ($delete === false) {
        return ['status' => 0, 'message' => 'Failed to delete stock entry product'];
    }

    // Delete voucher
    $deleteVoucher = $acc->voucher_delete($voucher['id']);
    if ($deleteVoucher === false) {
        return ['status' => 0, 'message' => 'Failed to delete voucher'];
    }

    // Delete product stock change log
    $deleteLog = $db->delete('product_stock_log', ['reference_id' => $id, 'change_type' => ST_CH_STOCK_ENTRY]);
    if ($deleteLog === false) {
        return ['status' => 0, 'message' => 'Failed to delete stock change log'];
    }

    // Commit the transaction if all steps were successful
    $db->transactionStop(true);
    // Return success
    return ['status' => 1, 'message' => 'Reject product successfully deleted'];
}

$general->createLog('stock_entry_deleted',$jArray);