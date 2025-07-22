<form  method="post" id="form" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" >	
	<fieldset>
		<input type="hidden" name="posting_officer_id" class="form-control" placeholder=" " value="<?php echo $staff['user_id']; ?>" maxlength="50" />
		<input type="hidden" name="posting_officer_name" class="form-control" placeholder=" " value="<?php echo $staff['full_name']; ?>" maxlength="50">
		<input type="hidden" name="income_line" value="<?php echo $income_line; ?>" maxlength="50">
		<input type="hidden" name="posting_officer_dept" value="<?php echo $menu['department']; ?>">
		
		
		<table class="table table-bordered">
			<tr> 
			<td colspan="3">
				<?php include 'payments/remittance_form_inc.php'; ?>

				<div class="form-group form-group-sm"> 
				  <label for="type" class="col-md-4 control-label">Type:</label>
					<div class="col-md-4 selectContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-list-alt"></i></span>
							<select name="type" class="form-control selectpicker" id="type_category" onChange="loadCalc()" required >
								<option value="" selected="selected">Select...</option>
								<option value="Vehicle">Vehicle</option>
								<option value="Forklift Operator">Forklift Operator</option>
								<option value="Artisan">Artisan</option>
							</select>
						</div>
					</div>	
					
					
					<div class="col-md-4 selectContainer" id="vehicle_div">					
						<div class="input-group">						
							<select name="vehicle_category" class="form-control selectpicker" id="vehicle_category" onChange="loadCalc()">
								<option value="">Select a category</option>
								<option value="Overnight Parking - 40 feet - N5000">Overnight Parking - 40 feet - N5000</option>
								<option value="Overnight Parking - OK Trucks - N2000">Overnight Parking - OK Trucks - N2000</option>
								<option value="Overnight Parking - LT Buses - N1500">Overnight Parking - LT Buses - N1500</option>
								<option value="Overnight Parking - Sienna - N1000">Overnight Parking - Sienna - N1000</option>
								<option value="Overnight Parking - Cars - N1000">Overnight Parking - Cars - N1000</option>
							</select>
						</div>
					</div>
					
					
					<div class="col-md-4 selectContainer" id="artisan_div">					
						<div class="input-group">						
							<select name="artisan_category" class="form-control selectpicker" id="artisan_category" onChange="loadCalc()">
								<option value="">Select a category</option>
								<option value="Welder/Welding Equipment">Welder/Welding Equipment</option>
								<option value="Carpenter">Carpenter</option>
								<option value="Bricklayer">Bricklayer</option>
								<option value="Others">Others</option>
							</select>
						</div>
					</div>
				</div>
		
				<!-- Text input-->			
				<div class="form-group form-group-sm" id="trans_desc_div">
					<label for="transaction_descr" class="control-label col-md-4">Transaction Description:</label>
					<div class="col-md-6 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-list-alt"></i></span>
							<input type="text" name="transaction_descr" class="form-control input-sm" placeholder="Transaction description" value="<?php if (isset($_POST['transaction_descr'])) echo @$transaction_descr; ?>" pattern=".{10,}" required />
						</div>
						
					</div>
				</div>
							
							
				
				
				<!-- Text input-->			
				<div class="form-group form-group-sm">
					<label for="no_of_nights" class="control-label col-md-4">No of Nights:</label>
					<div class="col-md-4 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-list-alt"></i></span>
							<input type="text" id="no_of_nights" name="no_of_nights" class="form-control input-sm" placeholder="No of Nights" value="<?php if (isset($_POST['no_of_tickets'])) echo @$no_of_nights; ?>" maxlength="4" onBlur="loadCalc()" required />
						</div>
					</div>
				</div>
				
				<div class="form-group form-group-sm" id="plate_no_div">
					<label for="plate_no" class="control-label col-md-4">Plate No:</label>
					<div class="col-md-4 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-list-alt"></i></span>
							<input type="text" id="plate_no" name="plate_no" class="form-control input-sm" placeholder="" value="<?php if (isset($_POST['plate_no'])) echo @$plate_no; ?>" onBlur="loadCalc()" maxlength="8" required />
						</div>
					</div>
				</div>
	
				<div class="form-group form-group-sm">
					<label for="receipt_no" class="control-label col-md-4">Receipt No:</label>
					<div class="col-md-4 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-tag"></i></span>
							<input type="text" name="receipt_no" class="form-control input-sm" placeholder="Receipt No" pattern="^\d{7}$" value="<?php if (isset($_POST['receipt_no'])) echo @$receipt_no; ?>" maxlength="7" onBlur="loadCalc()" required />
						</div>
					</div>
				</div>
						
				<div class="form-group form-group-sm">
					<label for="amount_paid" class="control-label col-md-4">Amount Remitted:</label>
					<div class="col-md-4 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon">&#8358;</span>
							<input type="text" id="amount_paid" name="amount_paid" class="form-control input-sm" placeholder="Amount Remitted" value="<?php if (isset($_POST['amount_paid'])) echo @$amount_paid; ?>" maxlength="20" onBlur="loadCalc()" readonly />
						</div>
					</div>
				</div>
				
						   
				<div class="form-group form-group-sm"> 
				  <label for="remitting_staff" class="col-md-4 control-label">Remitter's Name:</label>
					<div class="col-md-5 selectContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
							<select name="remitting_staff" class="form-control selectpicker" id="remitting_staff" required >
							  <option value="">Select...</option>
								<?php
								//if($menu["department"] == "Wealth Creation") {
									$query3 = "SELECT * FROM staffs ";
									$query3 .= "WHERE department = 'Wealth Creation' ";
									$query3 .= "ORDER BY full_name ASC ";
									$leasing_set = @mysqli_query($dbcon, $query3); 
									
									while ($leasing_officer = mysqli_fetch_array($leasing_set, MYSQLI_ASSOC)) {

									echo '<option value="'; ?><?php echo $leasing_officer['user_id']; ?><?php echo '-wc'; ?><?php echo '">'; ?><?php echo $leasing_officer['full_name']; ?><?php echo '</option>'; } 
								//}
								
									$query4 = "SELECT * FROM staffs_others ";
									$query4 .= "ORDER BY full_name ASC ";
									$leasing_set2 = @mysqli_query($dbcon, $query4); 
									
									while ($leasing_officer2 = mysqli_fetch_array($leasing_set2, MYSQLI_ASSOC)) {

									echo '<option value="'; ?><?php echo $leasing_officer2['id']; ?><?php echo '-so'; ?><?php echo '">'; ?><?php echo $leasing_officer2['full_name'].' - '.$leasing_officer2['department']; ?><?php echo '</option>'; }  
								?>
							</select>
						</div>
					</div>
				</div>
			
			</td>
			</tr>
			

			<?php include 'payments/submit_button_inc.php'; ?>
		</table>
	
	</fieldset>
</form>