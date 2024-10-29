<?php
namespace AdminBlockCountry;
if (!class_exists("ABCTomM8")) {
	class ABCTomM8 {
		// Creates a share website link for Facebook and Twitter.
		function add_social_share_links($url) {
			?>
			<a title="Share On Facebook" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo(esc_url($url)); ?>"><img style="width: 30px;" src="<?php echo(esc_url(get_option("siteurl"))); ?>/wp-content/plugins/admin-block-country/images/facebook.jpg" style="width: 30px;" /></a>
			<a title="Share On Twitter" target="_blank" href="http://twitter.com/intent/tweet?url=<?php echo(esc_url($url)); ?>"><img style="width: 30px;" src="<?php echo(esc_url(get_option("siteurl"))); ?>/wp-content/plugins/admin-block-country/images/twitter.jpg" style="width: 30px;" /></a>
			<a title="Rate it 5 Star" target="_blank" href="<?php echo(esc_url($url)); ?>"><img style="padding-bottom: 3px;" src="<?php echo(esc_url(get_option("siteurl"))); ?>/wp-content/plugins/admin-block-country/images/rate-me.png" /></a>

			<?php
		}

		// Return current url.
		function get_current_url() {
			$pageURL = 'http';
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}
			return $pageURL;
		}

		// Fixes up http post/get variables so that they present quotes correctly rather then like (\').
		function fix_http_quotes($http_data) {
			$http_data = str_replace('\"', "\"", $http_data);
			$http_data = str_replace("\'", '\'', $http_data);
			return sanitize_text_field($http_data);
		}

		// Basically gets the value from query string without having to use $_POST or $_GET variables. $_POST takes precidence over $_GET.
		function get_query_string_value($name, $index = -1) {
			$name = sanitize_text_field($name);
			$index = sanitize_text_field($index);
			if ($index == -1) {
				if (isset($_POST[$name])) {
					return $this->fix_http_quotes($_POST[$name]);
				} else if (isset($_GET[$name])) {
					return $this->fix_http_quotes($_GET[$name]);
				} else if (isset($_POST[$name."_0"])) {
					$i = 0;
					$data = "";
					do {
						$data .= sanitize_text_field($_POST[$name."_".$i]);
						if ($data != "") {
							$data .= " ";
						}
						$i++;
					} while (isset($_POST[$name."_".$i]));
					$_POST[$name] = $this->fix_http_quotes($data);
					return $this->fix_http_quotes($data);
				} else {
					return "";
				}
			} else {
				$name = str_replace("[]", "", $name);
				if (isset($_POST[$name][$index])) {
					return $this->fix_http_quotes($_POST[$name][$index]);
				} else if (isset($_GET[$name][$index])) {
					return $this->fix_http_quotes($_GET[$name][$index]);
				} else if (isset($_POST[$name."_0"][$index])) {
					$i = 0;
					$data = "";
					do {
						$data .= $_POST[$name."_".$i][$index];
						if ($data != "") {
							$data .= " ";
						}
						$i++;
					} while (isset($_POST[$name."_".$i][$index]));
					$_POST[$name][$index] = $this->fix_http_quotes($data);
					return $this->fix_http_quotes($data);
				} else {
					return "";
				}
			}
		}

		// Adds a form field to the page.
		function add_form_field($instance, $field_type, $field_label, $field_id, $field_name, $field_attributes = array(), $container_element, $container_attributes = array(), $value_options = array(), $field_index = -1) {

			$field_type = sanitize_text_field($field_type);
			$field_label = sanitize_text_field($field_label);
			$field_id = sanitize_text_field($field_id);
			$field_name = sanitize_text_field($field_name);
			$container_element = sanitize_text_field($container_element);
			$field_index = sanitize_text_field($field_index);

			$field_content = "";
			foreach ($field_attributes as $key => $value) {
				$field_content .= "$key='$value' ";
			}
			$container_content = "";
			foreach ($container_attributes as $key => $value) {
				$container_content .= "$key='$value' ";
			}

			if ($instance == null && preg_match("/^tomm8te_admin_option::/", $field_name)) {
				$field_name = str_replace("tomm8te_admin_option::", "", $field_name);
				$field_value = get_option($field_name);
				if (count($_POST) > 0) {
					if ($field_index >= 0) {
						$field_value = $this->get_query_string_value($field_name, $field_index);
					} else {
						$field_value = $this->get_query_string_value($field_name);
					}
				}
			} else {
				if (isset($instance->$field_name)) {
					$field_value = $instance->$field_name;
				} else {
					if ($instance == null || count($_POST) > 0) {
						if ($field_index >= 0) {
							$field_value = $this->get_query_string_value($field_name, $field_index);
						} else {
							$field_value = $this->get_query_string_value($field_name);
						}
					}
				}

			}

			$field_id_with_without_index = $field_id;
			$field_name_with_without_array = $field_name;
			$field_checkbox_array = "";
			if ($field_index >= 0) {
				$field_checkbox_array = "[".$field_index."]";
				$field_name_with_without_array .= "[]";
				$field_id_with_without_index .= "_".$field_index;
			}

			$field_type = strtolower($field_type);

			$field_value = esc_html($field_value);

			if (!is_array($field_value)) {
				$field_value = str_replace("&amp;", "&", htmlentities(htmlentities($field_value, ENT_NOQUOTES), ENT_QUOTES));
			}

			if ($field_type != "hidden") {
				echo("<$container_element $container_content>");
				if ($field_label != "") {
					if ($field_type == "checkbox") {
						echo("<label>".$field_label."<span class='colon'>:</span></label>");
					} else if ($field_type == "placeholder_text" || $field_type == "placeholder_textarea") {
						// Do nothing
					} else {
						echo("<label for='$field_id_with_without_index'>".$field_label."<span class='colon'>:</span></label>");
					}
				}
			}
			if ($field_type == "text") {
				echo("<input type='text' id='$field_id_with_without_index' name='$field_name_with_without_array' value='$field_value' $field_content />");
			} else if ($field_type == "hidden") {
				echo("<input type='hidden' id='$field_id_with_without_index' name='$field_name_with_without_array' value='$field_value' $field_content />");
			} else if ($field_type == "placeholder_text") {
				echo("<input type='text' id='".$field_id_with_without_index."' name='$field_name_with_without_array' value='$field_value' $field_content placeholder='".strip_tags($field_label)."' />");
			} else if ($field_type == "file") {
				echo("<input type='file' id='$field_id_with_without_index' name='".$field_name."[]' value='$field_value' $field_content />");
			} else if ($field_type == "textarea") {
				echo("<textarea id='$field_id_with_without_index' name='$field_name_with_without_array' ".$field_content.">$field_value</textarea>");
			} else if ($field_type == "placeholder_textarea") {
				echo("<textarea id='$field_id_with_without_index' name='$field_name_with_without_array' ".$field_content." placeholder='".strip_tags($field_label)."'>".$field_value."</textarea>");
			} else if ($field_type == "captcha") {
				echo("<img id='$field_id_with_without_index' src='".get_option("siteurl")."/wp-content/plugins/admin-block-country/securimage/securimage_show.php' />");
				echo("<a href='#' onclick=\"document.getElementById('".$field_id_with_without_index."').src = '".get_option("siteurl")."/wp-content/plugins/admin-block-country/securimage/securimage_show.php?' + Math.random(); return false\">[ Different Image ]</a><input type='text' name='".$field_name."' size='10' maxlength='6' />");
			} else if ($field_type == "select") {
				echo("<select id='$field_id_with_without_index' name='$field_name_with_without_array' ".$field_content.">");
				foreach($value_options as $key => $option) {
					$key = esc_html($key);
					$option = esc_html($option);
					if ($field_value == $key) {
						if ($key == "") {
							echo("<option selected label='Please Select Option'></option>");
						} else {
							echo("<option value='$key' selected>$option</option>");
						}
					} else {
						if ($key == "") {
							echo("<option label='Please Select Option'></option>");
						} else {
							echo("<option value='$key'>$option</option>");
						}
					}
				}
				echo("</select>");
			} else if ($field_type == "radio") {
				echo("<ul class='options'>");
				foreach($value_options as $key => $option) {
					$key = esc_html($key);
					$option = esc_html($option);
					$checked_value = "";
					if ($field_value == $key) {
						$checked_value = "checked";
					}
					echo("<li><input type='radio' id='".$field_name."_".$field_id_with_without_index."_".$key."' name='$field_name_with_without_array' value='$key' ".$field_content." ".$checked_value." /><label for='".$field_name."_".$field_id_with_without_index."_".$key."'>$option</label></li>");
				}
				echo("</ul>");
			} else if ($field_type == "checkbox") {
				echo("<ul class='options'>");
				if (count($value_options) == 1) {
					echo("<li><input type='hidden' name='".$field_name.$field_checkbox_array."' value='' />");
					$checked_value = "";
					foreach($value_options as $key => $option) {
						$key = esc_html($key);
						$option = esc_html($option);
						if ($field_value == $key) {
							$checked_value = "checked";
						}
						echo("<input type='checkbox' ".$checked_value." id='".$field_name."_".$field_id_with_without_index."_".$key."' name='".$field_name.$field_checkbox_array."' value='$key' ".$field_content." /><label for='".$field_name."_".$field_id_with_without_index."_".$key."'>$option</label></li>");
					}
				} else if (count($value_options) > 1) {
					$i = 0;
					foreach($value_options as $key => $option) {
						$key = esc_html($key);
						$option = esc_html($option);
						echo("<li><input type='hidden' name='".$field_name."_".$i.$field_checkbox_array."' value='' />");

						$field_value = $this->get_query_string_value($field_name."_".$i, $field_index);
						$field_value = str_replace("&amp;", "&", htmlentities(htmlentities($field_value, ENT_NOQUOTES), ENT_QUOTES));
						$checked_value = "";
						if (count($_POST) == 0) {
							if ($field_value == $key || (($field_value == "") && $instance && preg_match("/".$key." | ".$key."|^".$key."$/i", $instance->$field_name) )) {
								$checked_value = "checked";
							}
						}

						if ($field_value == $key) {
							$checked_value = "checked";
						}

						echo("<input type='checkbox' ".$checked_value." id='".$field_name."_".$field_id_with_without_index."_".$key."' name='".$field_name."_".$i.$field_checkbox_array."' value='$key' ".$field_content." /><label for='".$field_name."_".$field_id_with_without_index."_".$key."'>".$option."</label></li>");
						$i++;
					}
				}
				echo("</ul>");

			}

			if ($field_index >= 0) {
				$field_id = $field_id."_".$field_index;
			}
			if (isset($_SESSION[$field_id."_error"]) && $_SESSION[$field_id."_error"] != "") {
				echo "<span class='error'>".$_SESSION[$field_id."_error"]."</span>";
			}
			unset($_SESSION[$field_id."_error"]);

			if ($field_type != "hidden") {
				echo("</$container_element>");
			}
		}

		// Inserts data into the database.  Returns true if inserted correct, false if not.
		function insert_record($table_name, $insert_array) {
			global $wpdb;
			ob_start();
			$wpdb->show_errors();
			$table_name_prefix = $wpdb->prefix.sanitize_text_field($table_name);
			$wpdb->insert($table_name_prefix, $insert_array);
			$wpdb->print_error();
			$errors = ob_get_contents();
			ob_end_clean();

			if (preg_match("/<strong>WordPress database error:<\/strong> \[\]/", $errors)) {
				return true;
			} else {
				$sql = "SHOW INDEXES FROM $table_name_prefix WHERE non_unique =0 AND Key_name !=  'PRIMARY'";
				$results = $wpdb->get_results($sql);
				foreach ($results as $result) {
					$col_name = $result->Column_name;
					if (preg_match("/Duplicate entry (.+)&#039;".$col_name."&#039;]/", $errors, $matches, PREG_OFFSET_CAPTURE)) {
						if (!preg_match("/Must have a unique value/", $_SESSION[$col_name."_error"])) {
							$_SESSION[$col_name."_error"] .= "Must have a unique value.";
						}

					}
				}
				return false;
			}
		}

		// Updates data in the database. Returns true if updated correctly, false if not.
		function update_record_by_id($table_name, $update_array, $id_column_name, $id) {
			$id_column_name = sanitize_text_field($id_column_name);
			$id = sanitize_text_field($id);
			global $wpdb;
			ob_start();
			$wpdb->show_errors();
			$table_name_prefix = $wpdb->prefix.sanitize_text_field($table_name);
			$result = $wpdb->update($table_name_prefix, $update_array, array($id_column_name => $id));
			$wpdb->print_error();
			$errors = ob_get_contents();
			ob_end_clean();

			if (preg_match("/<strong>WordPress database error:<\/strong> \[\]/", $errors)) {
				return true;
			} else {
				$sql = "SHOW INDEXES FROM $table_name_prefix WHERE non_unique =0 AND Key_name !=  'PRIMARY'";
				$results = $wpdb->get_results($sql);
				foreach ($results as $result) {
					$col_name = $result->Column_name;
					if (preg_match("/Duplicate entry (.+)&#039;".$col_name."&#039;]/", $errors, $matches, PREG_OFFSET_CAPTURE)) {
						if (!preg_match("/Must have a unique value/", $_SESSION[$col_name."_error"])) {
							$_SESSION[$col_name."_error"] .= "Must have a unique value.";
						}
					}
				}
				return false;
			}
		}

		// Select records from the database. Returns sql results object.
		function get_results($table_name, $fields_array, $where_sql, $order_array = array(), $limit = "") {
			global $wpdb;
			$table_name_prefix = $wpdb->prefix.sanitize_text_field($table_name);
			if ($fields_array == "*") {
				$fields_comma_separated = "*";
			} else {
				$fields_comma_separated = sanitize_text_field(implode(",", $fields_array));
			}

			if (!empty($where_sql)) {
				$where_sql = "WHERE ".$where_sql;
			}
			$order_sql = "";
			if (!empty($order_array)) {
				$order_sql = "ORDER BY ".sanitize_text_field(implode(",", $order_array));
			}
			$limit_sql = "";
			if ($limit != "") {
				$limit_sql = "LIMIT ".sanitize_text_field($limit);
			}
			$sql = "SELECT $fields_comma_separated FROM $table_name_prefix $where_sql $order_sql $limit_sql";
			// echo $sql;
			return $wpdb->get_results($sql);
		}

	}
}
?>
