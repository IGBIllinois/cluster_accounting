<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class report {

	//create_excel_2003_report()
	//$data - double array - data values
	//$filename - string - name of the file to create
	//prompts to save an excel 2003 report.
	public static function create_excel_2003_report($data,$filename) {
		ob_clean();
		$excel_file = self::create_generic_excel($data);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename=' . $filename);
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel_file,'Xls');
		$writer->save('php://output');

	}

	//create_excel_2007_report()
	//$data - double array - data values
	//$filename = string - name of the file to create
	//prompts to save an excel 2007 report.
	public static function create_excel_2007_report($data,$filename) {
		ob_clean();
		$excel_file = self::create_generic_excel($data);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=" . $filename);
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($excel_file,'Xlsx');
		$writer->save('php://output');
	}

	//create_generic_excel()
	//$data - double array - data values
	//returns a PHPExcel object with data in correct columns and rows.
	//this function is used with create_excel_2007_report and create_excel_2003_report functions
	//to reuse common code.
	public static function create_generic_excel($data) {

		$excel_file = new Spreadsheet();
		$excel_file->setActiveSheetIndex(0);
		if (count($data) !== 0 ) {
			//Creates headers
			$headings = array_keys($data[0]);
			for ($i=0;$i<count($headings);$i++) {
				$excel_file->getActiveSheet()->setCellValueByColumnAndRow($i,1,$headings[$i]);
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getFont()->setBold(true);
				$excel_file->getActiveSheet()->getStyleByColumnAndRow($i,1)->getFont()->setUnderline(\PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE);
				$excel_file->getActiveSheet()->getColumnDimensionByColumn($i)->setAutoSize(true);
			}
			//Adds data
			$rows = count($data);
			$start_row = 2;
			foreach ($data as $row_data) {
				$column=0;
				foreach ($row_data as $key => $value) {
					$excel_file->getActiveSheet()->setCellValueByColumnAndRow($column,$start_row,$value);
					if (($key == 'Cost') || ($key == 'Billed Amount') || ($key == 'COST')) {
						$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$start_row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
					}
					else {
						$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$start_row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
						$excel_file->getActiveSheet()->getStyleByColumnAndRow($column,$start_row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
					}
					$column++;
				}
				$start_row++;
			}
		}
		$excel_file->getActiveSheet()->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
		$excel_file->getActiveSheet()->getPageSetup()->setFitToPage(true);
		return $excel_file;

	}

	//create_csv_report()
	//$data - double array of data
	//$filename - name of file to create
	//creates a csv file with data and prompts you to save it.
	public static function create_csv_report($data,$filename) {
		ob_clean();
		$delimiter = ",";
		$file_handle = fopen('php://output','w');
		$headings = array_keys($data[0]);
		ob_start();
		fputcsv($file_handle,$headings,$delimiter);
		foreach ($data as $row) {
			fputcsv($file_handle,$row,$delimiter);
		}
		fclose($file_handle);
		$result = ob_get_clean();
		//Sets headers then downloads the csv report file.
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Pragma: no-cache');
		echo $result;
	
	}

}
?>
