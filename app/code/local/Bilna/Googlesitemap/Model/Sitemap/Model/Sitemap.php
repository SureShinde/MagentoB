<?php
class Bilna_Googlesitemap_Model_Sitemap_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    const PROPERTY_LIMIT = 50000; // Google restrict number of entries in xml to 50k

    public function generateXml()
    {
        $count_of_property = 1;
        $number_of_file = 1;
        $io = $this->openXML($number_of_file++);
        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        $returnVars = $this->generateXmlFromCollections($baseUrl, $date, $changefreq, $priority, $collection, $io, $count_of_property, $number_of_file);
        $io = $returnVars["io"];
        $count_of_property = $returnVars["count_of_property"];
        $number_of_file = $returnVars["number_of_file"];
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        $returnVars = $this->generateXmlFromCollections($baseUrl, $date, $changefreq, $priority, $collection, $io, $count_of_property, $number_of_file);
        $io = $returnVars["io"];
        $count_of_property = $returnVars["count_of_property"];
        $number_of_file = $returnVars["number_of_file"];
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        $returnVars = $this->generateXmlFromCollections($baseUrl, $date, $changefreq, $priority, $collection, $io, $count_of_property, $number_of_file);
        $io = $returnVars["io"];
        $count_of_property = $returnVars["count_of_property"];
        $number_of_file = $returnVars["number_of_file"];
        unset($collection);

        $this->closeXML($io);

        $this->resetFilename($number_of_file);

        return $this;
    }

    public function generateXmlFromCollections($baseUrl, $date, $changefreq, $priority, $collections, $io, $count_of_property, $number_of_file)
    {
        foreach ($collections as $item) {
            $xml = sprintf(
                '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );

            if ($count_of_property++ > self::PROPERTY_LIMIT) {
                $count_of_property = 1;
                $this->closeXML($io);
                $io = $this->openXML($number_of_file++);
            }

            $io->streamWrite($xml);
        }

        return array("io" => $io, "count_of_property" => $count_of_property, "number_of_file" => $number_of_file);
    }

    public function openXML($number_of_file)
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));

        $filename = $this->generateFilename($number_of_file);

        if ($io->fileExists($filename) && !$io->isWriteable($filename)) {
            Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $filename, $this->getPath()));
        }

        $io->streamOpen($filename);

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        return $io;
    }

    public function closeXML($io)
    {
        $io->streamWrite('</urlset>');
        $io->streamClose();
    }

    public function generateFilename($number_of_file)
    {
        $filename = $this->getSitemapFilename();
        $filename = str_replace(".xml", "", $filename);
        $filename = ($number_of_file > 1) ? str_replace("_" . ($number_of_file-1), "", $filename) : $filename;
        $filename .= '_' . $number_of_file . '.xml';
        $this->setSitemapFilename($filename);

        return $filename;
    }

    public function resetFilename($number_of_file = 0)
    {
        $filename = $this->getSitemapFilename();
        if ($number_of_file > 1) {
            $filename = str_replace("_" . ($number_of_file - 1), "", $filename);
        }
        $this->setSitemapFilename($filename);
        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();
    }
}
