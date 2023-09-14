<?php
/**
 * Copyright (C) 2015 Team e-DDC
 * Mohamad Rotmianto (rotmiantomohamad@gmail.com)
 * Eko Wahyudi (waaah.you92@gmail.com)
 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */
/* e-DDC for SLiMS, update 07-11-2015*/ 
?>
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="http://www.e-ddc.org" target="blank"><span class="glyphicon glyphicon-bookmark"></span> e-DDC Edition 23</a>
    </div>
	<ul class="nav navbar-nav navbar-right">
        <li><a href="#" data-toggle="modal" data-target="#pop"><span class="glyphicon glyphicon-info-sign"></span></a>
		</li>
      </ul>
  </div>
      <ul class="nav nav-tabs nav-pills">
            <li class="active"><a href="#home" data-toggle="tab"><i class="glyphicon glyphicon-home"></i> Home</a> </li>
            <li><a href="#content" data-toggle="tab"><i class="glyphicon glyphicon-book"></i> Content</a></li>
            <li><a href="#tables" data-toggle="tab"><i class="glyphicon glyphicon-list-alt"></i> Tables</a></li>
            <li><a href="#glossary" data-toggle="tab"><i class="glyphicon glyphicon-file"></i> Glossary</a></li>
            <li><a href="#about" data-toggle="tab"><i class="glyphicon glyphicon-bullhorn"></i> About</a></li>
        </ul>
  </nav>
  <!-- Content -->

	<br><br><br><br><br><br/>
	<div class="col-sm-12">
        <div class="tab-content">
		
            <!-- Home -->
            <div class="tab-pane active" id="home">
                                <?php
									$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='ddc_home'");
									while ($content = $ddc->fetch_row()) {
									echo"$content[0]";	}
									?>
                            
            </div>
			
            <!-- Table e-DDC -->
            <div class="tab-pane" id="content">
                                <table id="ddc" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="15%">Class</th>
                                            <th>About</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
										// Get e-DDC data from Databse
											$ddc = $dbs->query("SELECT clas, about FROM ddc_db");
											while ($data = $ddc->fetch_row()) {
												echo"<tr>
													<td>$data[0]</td><td>$data[1]</td>
												</tr>";
											}
										?>
                                    </tbody>
                                </table>
                            
            </div>
            <!-- Tables -->
            <div class="tab-pane" id="tables">
                                <div class="col-sm-3">
                                    <div class="sidebar-nav">
                                                <ul class="nav nav-pills">
                                                    <li class="active"><a href="#table1" data-toggle="tab"><i class="glyphicon glyphicon-log-in"></i> Table 1</a></li>
                                                    <li><a href="#table2" data-toggle="tab"><i class="glyphicon glyphicon-log-in"></i> Table 2</a></li>
                                                    <li><a href="#table3" data-toggle="tab"><i class="glyphicon glyphicon-log-in"></i> Table 3</a></li>
                                                    <li><a href="#table4" data-toggle="tab"><i class="glyphicon glyphicon-log-in"></i> Table 4</a></li>
                                                    <li><a href="#table5" data-toggle="tab"><i class="glyphicon glyphicon-log-in"></i> Table 5</a></li>
                                                    <li><a href="#table6" data-toggle="tab"><i class="glyphicon glyphicon-log-in"></i> Table 6</a></li>
                                                </ul>
                                    </div>
                                </div>
                                <div class="col-sm-9">
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="table1">
                                                    <?php
														$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='table1'");
														while ($content = $ddc->fetch_row()) {
															echo"$content[0]";
														}
													?>
                                                </div>
                                                <div class="tab-pane" id="table2">
                                                    <?php
														$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='table2'");
														while ($content = $ddc->fetch_row()) {
															echo"$content[0]";
														}
													?>
                                                </div>
                                                <div class="tab-pane" id="table3">
                                                    <?php
														$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='table3'");
														while ($content = $ddc->fetch_row()) {
															echo"$content[0]";
														}
													?>
                                                </div>
                                                <div class="tab-pane" id="table4">
                                                    <?php
														$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='table4'");
														while ($content = $ddc->fetch_row()) {
															echo"$content[0]";
														}
													?>
                                                </div>
                                                <div class="tab-pane" id="table5">
                                                    <?php
														$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='table5'");
														while ($content = $ddc->fetch_row()) {
															echo"$content[0]";
														}
													?>
                                                </div>
                                                <div class="tab-pane" id="table6">
                                                    <?php
														$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='table6'");
														while ($content = $ddc->fetch_row()) {
															echo"$content[0]";
														}
													?>
                                                </div>
                                    </div>
                                </div>
                            
            </div>
			
            <!-- Glossary -->
            <div class="tab-pane" id="glossary">
                                <div class="col-sm-6">
                                        <?php
											$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='glossary1'");
											while ($content = $ddc->fetch_row()) {
												echo"$content[0]";
											}		
										?>
                                </div>
								<div class="col-sm-6">
                                        <?php
											$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='glossary2'");
											while ($content = $ddc->fetch_row()) {
												echo"$content[0]";
											}
										?>
                                    </div>
                            
            </div>
			
            <!-- About -->
            <div class="tab-pane" id="about">
                            <?php
								$ddc = $dbs->query("SELECT content_text FROM ddc_content WHERE content_id ='ddc_about'");
								while ($content = $ddc->fetch_row()) {
									echo"$content[0]";
								}
							?>
                        </div>
					</div>
                </div>