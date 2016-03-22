<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollectionprofile_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Wishlistcollectionprofile_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollectionprofile_Rest
{
    /** 
     * Update collection profile by username.
     * 
     * bodyParams:
     * {"dob":"01\/06\/1983","location":"asdasd","about":"asd", "image_url":"http://pathtoimageurl"}
     * 
     * Method was use create function since, logan didnt need to send entity_id as param
     */
    protected function _create(array $data)
    {
        $username = $this->getRequest()->getParam('username');
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        if (!$profiler->getCustomerId()) {
            $this->_critical('Current username is not found.');
        }
        $customerId = $profiler->getCustomerId();
        $customer = $this->_loadCustomerById($customerId);

        if ($customer->getId()) {
            # Check if request is a post request
            try {

                if (!isset($data['dob']) || $data['dob'] == "") {
                    $this->_critical('Please provide DOB.');
                } 
                if (!isset($data['location']) || $data['location'] == "") {
                    $this->_critical('Please provide location.');
                }
                if (!isset($data['about']) || $data['about'] == "") {
                    $this->_critical('Please provide about information.');
                }

                # Get username and slug it
                $postUsername = Mage::getModel('catalog/product_url')->formatUrlKey($username);

                $postAbout = $data['about'];
                $postLocation = $data['location'];
                $postDob = $data['dob'];

                # Check if user has temporary profile and going to use different username
                if ($profiler->getTemporary() && $profiler->getUsername() != $postUsername) {

                    # Data validation
                    try {
                        Mage::helper('socialcommerce')->validateInput();
                    } catch (Exception $e) {
                        $this->_critical($e->getMessage());
                    }

                    # Check if username is still available
                    $usernameAvailable = Mage::helper('socialcommerce')->checkUsernameAvailable($postUsername);

                    if (! $usernameAvailable) {
                        $this->_critical("Username not available"); # Username not available
                    }

                    $oldUsername = $profiler->getUsername();

                    $profiler->setUsername($postUsername);
                }

                # Check image submission
                $postAvatar = Mage::helper('socialcommerce')->processAvatar($customerId, $data);

                # Assign updated data
                $profiler->setStatus(1);
                $profiler->setWishlist(1);
                $profiler->setTemporary(0);
                $profiler->setAbout($postAbout);
                $profiler->setLocation($postLocation);

                if ($postAvatar) {
                    //$profile->setCloudAvatar($postAvatar);
                    $profiler->setAvatar($postAvatar);
                }

                $profiler->save();

                if ($postDob) {
                    $tmp = new DateTime($postDob);
                    $customer->setDob($tmp->format('Y-m-d H:i:s'));
                    $customer->save();
                }

            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        } else {
            $this->_critical('No customer account specified.');
        }
    }
}
