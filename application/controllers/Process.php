<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('product_model', 'productModel');
        $this->load->model('brand_model', 'brandModel');
        $this->load->model('maker_model', 'makerModel');
        $this->load->model('product_maker_model', 'productMakerModel');
        $this->load->model('product_brand_model', 'productBrandModel');
        $this->load->model('product_tag_model', 'productTagModel');
        $this->load->model('product_attribute_model', 'productAttributeModel');
    }

    public function loadBrands()
    {
        $brands = $this->getDataFromFile('brand');
        $dataPacket = [];
        foreach ($brands as $b) {
            $dataPacket[] = ['name' => $b];
        }
        $this->brandModel->insertBatch($dataPacket);
        returnResponse(count($dataPacket) . " brands inserted");
    }
    public function loadMakers()
    {
        $makers = $this->getDataFromFile('maker');
        $dataPacket = [];
        foreach ($makers as $m) {
            $dataPacket[] = ['name' => $m];
        }
        $this->makerModel->insertBatch($dataPacket);
        returnResponse(count($dataPacket) . " makers inserted");
    }
    // get all brands or makers from file based on type
    protected function getDataFromFile($type = null)
    {
        if ($type != 'maker' && $type != 'brand') {
            throwError(BAD_REQUEST, 'Load maker or brand');
        }

        $filename = "data/input_data.jsonl";
        if (!file_exists($filename)) {
            throwError(ITEM_NOT_FOUND, 'File not found');
        }
        $file = fopen($filename, 'r');
        $data = [];
        while (($line = fgets($file)) !== false) {
            $dataArray = json_decode($line, true);

            // check if maker/brand presesnt
            if (!isset($dataArray[$type]) || empty($dataArray[$type])) {
                continue;
            }
            $typeValue = strtolower($dataArray[$type]);

            // check if already in the array
            if (!in_array($typeValue, $data))
                $data[] = $typeValue;
        }
        fclose($file);
        return $data;
    }

    public function loadProducts()
    {
        $fileNumber = $this->input->get('file_number');
        $filename = "data/input_data_{$fileNumber}.jsonl";
        if (!file_exists($filename)) {
            throwError(ITEM_NOT_FOUND, 'File not found');
        }
        $file = fopen($filename, 'r');
        $brands = $this->mapIdWithName($this->brandModel->getAll());
        $makers = $this->mapIdWithName($this->makerModel->getAll());
        $products = $productAttributes = $productTags = $productMakers = $productBrands = [];

        while (($line = fgets($file)) !== false) {
            $data = json_decode($line, true);

            $jan = $data['jan'];
            $productName = $data['product_name'] ?? null;
            $attributes = $data['attributes'] ??  null;
            $maker = $data['maker'] ?? null;
            $brand = $data['brand'] ?? null;
            $tagsFromDescription = $data['tags_from_description'] ?? null;
            $tagsFromReview = $data['tags_from_review'] ?? null;

            $products[] = [
                'jan_code' => $jan,
                'name' => $productName
            ];

            if ($brand) {
                $tmpBrand = strtolower($brand);
                // get brand id from loaded brands, if not found insert as new brand get ID
                $brandId = isset($brands[$tmpBrand])
                    ? $brands[$tmpBrand]
                    : $this->brandModel->insert(['name' => $tmpBrand]);
                $productBrands[] = [
                    'jan_code' => $jan,
                    'brand_id' => $brandId
                ];
            }

            if ($maker) {
                $tmpMaker = strtolower($maker);
                // get maker id from loaded makers, if not found insert as new maker get ID
                $makerId = isset($makers[$tmpMaker])
                    ? $makers[$tmpMaker]
                    : $this->makerModel->insert(['name' => $tmpMaker]);
                $productMakers[] = [
                    'jan_code' => $jan,
                    'maker_id' => $makerId
                ];
            }

            $productAttributes[] = [
                'jan_code' => $jan,
                'attributes' => json_encode($attributes)
            ];

            if ($tagsFromDescription) {
                $productTags[] = [
                    'jan_code' => $jan,
                    'tags' => json_encode($tagsFromDescription),
                    'extracted_from' => TAG_FROM_DESCRIPTION
                ];
            }

            if ($tagsFromReview) {
                $productTags[] = [
                    'jan_code' => $jan,
                    'tags' => json_encode($tagsFromDescription),
                    'extracted_from' => TAG_FROM_REVIEW
                ];
            }
        }
        fclose($file);
        $messages = [];
        //insert products
        if (count($products) > 0) {
            $this->productModel->insertBatch($products);
            $messages[] = count($products) . " product(s) inserted";
        }

        //insert brands
        if (count($productBrands) > 0) {
            $this->productBrandModel->insertBatch($productBrands);
            $messages[] = count($productBrands) . " product brand relation(s) inserted";
        }

        //insert makers
        if (count($productMakers) > 0) {
            $this->productMakerModel->insertBatch($productMakers);
            $messages[] = count($productBrands) . " product maker relation(s) inserted";
        }

        // insert attributes
        if (count($productAttributes) > 0) {
            $this->productAttributeModel->insertBatch($productAttributes);
            $messages[] = count($productAttributes) . " product attributes inserted";
        }

        // insert tags
        if (count($productTags) > 0) {
            $this->productTagModel->insertBatch($productTags);
            $messages[] = count($productAttributes) . " product tags inserted";
        }
        returnResponse($messages);
    }

    public function splitData()
    {
        $sourceFile = 'data/input_data.jsonl';
        $linesPerFile = 10000; // lines per output file

        $file = fopen($sourceFile, 'r');
        if (!$file) {
            die("Error: Unable to open the source file.");
        }

        $data = [];
        $fileCount = 1;

        while (($line = fgets($file)) !== false) {
            $data[] = $line;

            if (count($data) >= $linesPerFile) {
                // Writting the buffer to a new JSONL file
                $this->writeToFile($fileCount, $data);

                // Reset the buffer and increment the file count
                $data = [];
                $fileCount++;
            }
        }

        // Write any remaining lines to the last file
        if (!empty($data)) {
            $this->writeToFile($fileCount, $data);
            $fileCount++;
        }
        $fileCount--;
        // Close the source file handle
        fclose($file);
        returnResponse("{$fileCount} files created.");
    }
    protected function writeToFile($fileCount, $dataArray)
    {
        $targetFile = "data/input_data_$fileCount.jsonl";
        $data = implode('', $dataArray);
        file_put_contents($targetFile, $data);
    }
    protected function mapIdWithName($data)
    {
        if (empty($data)) return [];
        $idMap = [];
        foreach ($data as $d) {
            $idMap[$d['name']] = $d['id'];
        }
        return $idMap;
    }
}
