<?php
namespace Frontend\Widgets;
/**
 * Description of BaseWidget
 *
 * @author mariovalentino
 */
class AddressWidget extends BaseWidget{

    /**
     * getting content widget.
     */
    public function getContent($return = FALSE){
        $page   = $this->params['page'];
        $limit  = $this->params['limit'];
        
        $addressData['conditions']  = [ ['field' => 'customerId', 'filter' => '=', 'value' => $this->params['customerId']] ];
        $addressData['limit']       = $limit;
        if($page != NULL)
            $addressData['page']        = $page;
        $addressList                = (new CustomerModel())->getAddresses($addressData);
        $totalData                  = $addressList['total_record'];
        $totalDataPerPage           = count($addressList['addresses']);
        
        
        
        $url = 'customer/addresses?page=';
        unset($addressData);
        $widgetHtml = '';
        $i = 1;
        if($addressList['addresses']){
            foreach($addressList['addresses'] as $key => $data){
                $addressDetail['id'] = $data['id'];
                $addressDetailAPI    = (new CustomerModel())->getAddress($addressDetail);
                
                $additional          = explode('|',$addressDetailAPI['additionalInfo']);
                $addressDetailAPI['additional'] = isset($additional[0]) != NULL ? $additional[0] : NULL;
                $addressDetailAPI['building']   = isset($additional[1]) != NULL ? $additional[1] : NULL;
                $addressDetailAPI['floor']      = isset($additional[2]) != NULL ? $additional[2] : NULL;
                $addressDetailAPI['block']      = isset($additional[3]) != NULL ? $additional[3] : NULL;
                $addressDetailAPI['company']    = isset($additional[4]) != NULL ? $additional[4] : NULL;
                $addressDetailAPI['count']      = $i;

                
                $redisHash      = $this->type.'/'.CustomerModel::$getAddressURL;
                $redisKey       = $this->params['customerId'].'|'.$addressDetailAPI['addressId'];

                $responseData   = $this->getRedisData($redisHash, $redisKey);
                if($responseData){
                    $widgetHtml .= $responseData;
                }else{
                    $html        = $this->setView('address', ['data' => $addressDetailAPI]);
                    $this->setRedisData($redisHash, $redisKey, $html);
                    $widgetHtml .= $html;
                }

                $i++;
            }
        }
        
        if($page != NULL){
            // building pagination.
            $pagingObj  = new BilnaPagination($this->di);
            $pagingObj->setBaseUrl($url);
            $pagingObj->setPage($page);
            $pagingObj->setLimit($limit);
            $pagingObj->setTotalData($totalData);
            $pagingObj->setTotalDataPerPage($totalDataPerPage);
            //$pagingObj->setType('addresses');
            
            
            if($totalData > 0){
                $widgetHtml  .= $pagingObj->setPagination();
            }
        }
        echo $widgetHtml;
    }
}
