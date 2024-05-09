class getGoogleMap
{
	private $current_num;
	private $addr;
	private $lat_long;
	private $bukken_num;

	public function set_googlemap_data() {
		$postid = $_GET['postid'];

		$this->addr = get_post_meta($postid, '都道府県名', TRUE);
		$this->addr .= get_post_meta($postid, '所在地名1', TRUE);
		$this->addr .= get_post_meta($postid, '所在地名2', TRUE);
		$this->addr .= get_post_meta($postid, '所在地名3', TRUE);

		$this->lat_long = get_post_meta($postid, '緯度', TRUE);
		$this->lat_long .= "," .get_post_meta($postid, '経度', TRUE);

		$this->bukken_num = get_post_meta($postid, '物件番号', TRUE);

		$gmimg_url = $this->get_googlemap_image($this->addr, $this->lat_long);
		$this->save_image($gmimg_url, $postid);
	}

	private function get_googlemap_image($addr, $lat_long) {
		if (!$lat_long) {
			$gmdata = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' .$addr. '&language=ja&sensor=false');
			$gmdata = json_decode($gmdata);
			$gmdata = $gmdata->results;
			$gmdata = $gmdata[0]->geometry;
			$gmdata = $gmdata->location;
			$lat = $gmdata->lat;
			$long = $gmdata->lng;
		} else {
			$sepa = explode(",", $lat_long);
			$lat = $sepa[0];
			$long = $sepa[1];
		}
		$gmimg_url = "http://maps.google.com/maps/api/staticmap?center=" .$lat. "," .$long. "&language=ja&zoom=16&size=800x600&markers=color:red|" .$lat. "," .$long. "&sensor=true
		";
		$gmimg_url = preg_replace("/(\s|　)/", "", $gmimg_url);
		echo $this->bukken_num. "<br>";
		echo "<img src=\"".$gmimg_url."\">";
		return $gmimg_url;
	}

	private function save_image($gmimg_url, $postid) {
		// WP DB用
		global $wpdb;
		$pref = get_post_meta($postid, '都道府県名', TRUE);
		$pref_cd = $wpdb->get_var($wpdb->prepare("
			SELECT		*
			FROM		pref
			WHERE		pref_name = '" .$pref. "'
			", $pref
		));

		$remote_image = fopen($gmimg_url, "rb");
		if ($remote_image) {
			while (!feof($remote_image)) {
				$image_data .= fread($remote_image, 1024);
			}
		}

		if (!is_dir("IMAGEDIR" .$pref_cd)) {
			mkdir("IMAGEDIR" .$pref_cd);
		}
		$local_dir = "IMAGEDIR" .$pref_cd. "/" .$this->bukken_num. ".png";
		$local_image = fopen($local_dir, "wb");
		fwrite($local_image, $image_data);

		$this->current_num++;
	    $file = "FILENAME";
	    $fp = fopen($file, "w+");
		fwrite($fp, $this->current_num);
		fclose($fp);
	}

}
