#!/bin/bash

for year in 2017 2018 2019 2020 2021 2022
do
	for month in 01 02 03 04 05 06 07 08 09 10 11 12
	do
		./calc_bill.php --year=${year} --month=${month}
	done

done
