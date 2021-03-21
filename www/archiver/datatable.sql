CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `value_no` int(11) NOT NULL,
  `value_txt` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;