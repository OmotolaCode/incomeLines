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
					<label for="transaction_descr" class="control-label col-md-4">Transaction Description:</label>
					<div class="col-md-7 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-list-alt"></i></span>
							<input type="text" name="transaction_descr" class="form-control input-sm" placeholder="Transaction description" value="<?php if (isset($_POST['transaction_descr']) || isset($income_line_desc)) echo @$transaction_descr; ?>" pattern=".{6,}" readonly />
						</div>
					</div>
				</div>
				
				<div class="form-group form-group-sm"> 
				  <label for="ticket_category" class="col-md-4 control-label">Toilet:</label>
					<div class="col-md-4 selectContainer">
						<div class="input-group">
							<span class="input-group-addon">&#8358;</span>
							<select name="ticket_category" class="form-control selectpicker" id="ticket_category" required >
							  <option value="A Block Toilet">A Block Toilet</option>
							  <option value="Buka Toilet">Buka Toilet</option>
							  <option value="Center Toilet">Center Toilet</option>
							  <option value="Exit Toilet">Exit Toilet</option>
							  <option value="Pedestrian Toilet">Pedestrian Toilet</option>
							</select>
						</div>
					</div>
				</div>
	
				<div class="form-group form-group-sm">
					<label for="receipt_no" class="control-label col-md-4">Receipt No:</label>
					<div class="col-md-4 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-tag"></i></span>
							<input type="text" name="receipt_no" class="form-control input-sm" placeholder="Receipt No" pattern="^\d{7}$" value="<?php if (isset($_POST['receipt_no'])) echo @$receipt_no; ?>" maxlength="7" required />
						</div>
					</div>
				</div>
						
						
				<div class="form-group form-group-sm">
					<label for="amount_paid" class="control-label col-md-4">Amount Remitted:</label>
					<div class="col-md-4 inputGroupContainer">
						<div class="input-group">
							<span class="input-group-addon">&#8358;</span>
							<input type="text" id="amount_paid" name="amount_paid" class="form-control input-sm" placeholder="Amount Remitted" value="<?php if (isset($_POST['amount_paid'])) echo @$amount_paid; ?>" maxlength="20" required />
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
			

			<?php 
				if ($menu["department"] == "Wealth Creation" || $menu["level"] == "ce"){ 
				echo '
					<input type="hidden" name="debit_alias" value="till" maxlength="50">
					<input type="hidden" name="credit_alias" value="'.$alias.'" maxlength="50">';
				} 
			 
				if ($menu["department"] == "Accounts"){ 
			?>
				<tr>
					<td colspan="3">		   
					<div class="form-group form-group-sm"> 
						<div class="col-sm-5 col-sm-offset-4 selectContainer">
							<div class="input-group">
								<span class="input-group-addon">DR:</span>
								<select name="debit_account" class="form-control selectpicker" id="debit_account" required>
									<option value="">Select debit account</option>
									<option value="10103">Account Till</option> 
									<option value="10150">Wealth Creation Funds Account</option> 
								</select>
							</div>
						</div>
					</div>


					<div class="form-group form-group-sm"> 
						<div class="col-sm-5 col-sm-offset-4 selectContainer">
							<div class="input-group">
								<span class="input-group-addon">CR:</span>
								<select name="credit_account" class="form-control selectpicker" id="credit_account" required>
									<option value="">Select credit account</option>
									<?php
										$cquery = "SELECT * FROM accounts ";
										$cquery .= "WHERE active = 'Yes' ";
										$cquery .= "AND acct_alias = '$alias' ";
										$caccount_set = mysqli_query($dbcon, $cquery); 
										
										$caccount = mysqli_fetch_array($caccount_set, MYSQLI_ASSOC);
									?>
									<option value="<?php echo $caccount['acct_id']; ?>"><?php echo ucwords(strtolower($caccount['acct_desc'])); ?></option> 
								</select>
							</div>
						</div>
					</div>
					</td>
				</tr>
				<?php
					}
				?>

				<tr>
					<td colspan="3" align="center">
						<div class="form-group form-group-sm">
							<div>
								<?php
								/*
									if ($current_time >= $wc_begin_time && $current_time <= $wc_end_time && $menu["department"] == "Wealth Creation"){
										echo '<h4><span style="color:#ec7063; font-weight:bold;">Posting automatically disabled till tomorrow!</span></h4>';
									} elseif (@$amt_remitted <= 0 && $menu["department"] == "Wealth Creation") {
										echo '<h4><span style="color:#ec7063; font-weight:bold;">You do not have any unposted remittances for today.</h4>';
									} else {
										if($no_of_declined_post == 0 && $it_status == 0) {
											echo '
											<button type="submit" name="btn_post_'.$income_line.'" class="btn btn-danger btn-sm">Post '.$income_line_desc.' <span class="glyphicon glyphicon-send"></span></button>
											<button type="reset" name="btn_clear" class="btn btn-primary btn-sm">Clear</button>';
										} else {
											if($no_of_declined_post > 0) {
												echo '<h4><span style="color:#ec7063; font-weight:bold;">You have '.$no_of_declined_post.' DECLINED POSTS from previous transactions.';
											}
											if ($it_status > 0) {
												echo '<h4><span style="color:#ec7063; font-weight:bold;">You have '.$it_status.' WRONG postings with errors from previous transactions.';
											}
											echo 'Kindly treat before proceeding to post fresh transactions. Thanks.</span></h4>';
										}
									}
								*/
								?>
								
								<?php
									if (@$amt_remitted <= 0 && $menu["department"] == "Wealth Creation") {
										echo '<h4><span style="color:#ec7063; font-weight:bold;">You do not have any unposted remittances for today.</h4>';
									} else {
										if($no_of_declined_post == 0 && $it_status == 0) {
											echo '
											<button type="submit" name="btn_post_'.$income_line.'" class="btn btn-danger btn-sm">Post '.$income_line_desc.' <span class="glyphicon glyphicon-send"></span></button>
											<button type="reset" name="btn_clear" class="btn btn-primary btn-sm">Clear</button>';
										} else {
											if($no_of_declined_post > 0) {
												echo '<h4><span style="color:#ec7063; font-weight:bold;">You have '.$no_of_declined_post.' DECLINED POSTS from previous transactions.';
											}
											if ($it_status > 0) {
												echo '<h4><span style="color:#ec7063; font-weight:bold;">You have '.$it_status.' WRONG postings with errors from previous transactions.';
											}
											echo 'Kindly treat before proceeding to post fresh transactions. Thanks.</span></h4>';
										}
									}
								?>
							</div>
						</div>
					</td>
				</tr>
		</table>
	
	</fieldset>
</form>