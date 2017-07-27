<?php

class Paylike_Payment_IndexController extends Mage_Core_Controller_Front_Action {

    public function paymentAction() {
        $order_id = $this->getRequest()->getParam('order_id');
        $paylike_action = $this->getRequest()->getParam('paylike_action');
        $orderObject = Mage::getModel('sales/order')->load($order_id);
        $order_data = $orderObject->getData();

        $response = array();
        switch ($paylike_action) {
            case 'capture' :
                $response = Mage::getModel('paylike_payment/paylike')->capture($orderObject->getPayment(), $order_data['grand_total'], true);
                break;
            case 'refund' :
                $paylike_amount_to_refund = $this->getRequest()->getParam('paylike_amount_to_refund');
                $paylike_refund_reason = $this->getRequest()->getParam('paylike_refund_reason');

                $response = Mage::getModel('paylike_payment/paylike')->refund($orderObject->getPayment(), $paylike_amount_to_refund, $paylike_refund_reason, true);
                break;
            case 'void' :
                $response = Mage::getModel('paylike_payment/paylike')->void($orderObject->getPayment(), true);
                break;
        }

        $jsonData = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    public function uploadLogoAction() {
        $logo_name = $this->getRequest()->getParam('logo_name');
        if (empty($logo_name)) {
            $response = array(
                'status' => 0,
                'message' => 'Please give logo name.'
            );
            $jsonData = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody($jsonData);
        } else {
            $logo_slug = strtolower(str_replace(' ', '-', $logo_name));
            $paylike_logos = Mage::getModel('paylike_payment/paylikelogos')
                    ->getCollection()
                    ->addFieldToFilter('slug', $logo_slug)
                    ->getFirstItem()
                    ->getData();
            if (!empty($paylike_logos)) {
                $response = array(
                    'status' => 0,
                    'message' => 'The name added for the logo is already in use. Please try another name.'
                );
                $jsonData = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody($jsonData);
            } else {

                if (!empty($_FILES['logo_file']['name'])) {
                    $target_dir = Mage::getBaseDir('skin') . Paylike_Payment_Model_Paylikelogos::PAYMENT_LOGO_PATH;
                    $name = basename($_FILES['logo_file']["name"]);
                    $path_parts = pathinfo($name);
                    $extension = $path_parts['extension'];
                    $file_name = $logo_slug . '.' . $extension;
                    $target_file = $target_dir . basename($file_name);
                    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

                    // Check if file already exists
                    if (file_exists($target_file)) {
                        $response = array(
                            'status' => 0,
                            'message' => 'Sorry, file already exists.'
                        );
                        $jsonData = Mage::helper('core')->jsonEncode($response);
                        $this->getResponse()->setHeader('Content-type', 'application/json');
                        $this->getResponse()->setBody($jsonData);
                    } else {
                        // Allow certain file formats
                        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "svg"
                        ) {
                            $response = array(
                                'status' => 0,
                                'message' => 'Sorry, only JPG, JPEG, PNG, GIF & SVG files are allowed.'
                            );
                            $jsonData = Mage::helper('core')->jsonEncode($response);
                            $this->getResponse()->setHeader('Content-type', 'application/json');
                            $this->getResponse()->setBody($jsonData);
                        } else {
                            if (move_uploaded_file($_FILES['logo_file']["tmp_name"], $target_file)) {
                                $save_data = array(
                                    'name' => $logo_name,
                                    'slug' => $logo_slug,
                                    'file_name' => $file_name,
                                    'default_logo' => '0',
                                    'created_at' => date('Y-m-d H:i:s')
                                );
                                $paylikeLogosModel = Mage::getModel('paylike_payment/paylikelogos');
                                try {
                                    $res = $paylikeLogosModel->setData($save_data)->save();
                                    if ($res) {
                                        $response = array(
                                            'status' => 1,
                                            'message' => "The file \"" . basename($file_name) . "\" has been uploaded."
                                        );
                                        $jsonData = Mage::helper('core')->jsonEncode($response);
                                        $this->getResponse()->setHeader('Content-type', 'application/json');
                                        $this->getResponse()->setBody($jsonData);
                                    } else {
                                        unlink($target_file);
                                        $response = array(
                                            'status' => 0,
                                            'message' => "Oops! An error occurred while saving the logo. Please try again."
                                        );
                                        $jsonData = Mage::helper('core')->jsonEncode($response);
                                        $this->getResponse()->setHeader('Content-type', 'application/json');
                                        $this->getResponse()->setBody($jsonData);
                                    }
                                } catch (Exception $e) {
                                    $response = array(
                                        'error' => 1,
                                        'message' => $e->getMessage()
                                    );
                                    $jsonData = Mage::helper('core')->jsonEncode($response);
                                    $this->getResponse()->setHeader('Content-type', 'application/json');
                                    $this->getResponse()->setBody($jsonData);
                                }
                            } else {
                                $response = array(
                                    'status' => 0,
                                    'message' => 'Sorry, there was an error uploading your file.'
                                );
                                $jsonData = Mage::helper('core')->jsonEncode($response);
                                $this->getResponse()->setHeader('Content-type', 'application/json');
                                $this->getResponse()->setBody($jsonData);
                            }
                        }
                    }
                } else {
                    $response = array(
                        'status' => 0,
                        'message' => 'Please select a file for upload.'
                    );
                    $jsonData = Mage::helper('core')->jsonEncode($response);
                    $this->getResponse()->setHeader('Content-type', 'application/json');
                    $this->getResponse()->setBody($jsonData);
                }
            }
        }
    }

    public function getSiteNameAction() {
        $pop_up_title = Mage::getStoreConfig('payment/paylike/pop_up_title');
        if ($pop_up_title != '') {
            echo $pop_up_title;
        } else {
            echo Mage::app()->getStore()->getFrontendName();
        }
    }

}
