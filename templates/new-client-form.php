<div class="tax__support_container">
    <!-- Main Landing -->
    <header id="welcome-header">
        <h1>TAX SUPPORT</h1>
        <p>Empowering your financial journey with reliable services.</p>
    </header>

    <div id="selection-view" class="active-view">
        <div class="selection-grid">
            <div class="portal-card" onclick="startForm('new')">
                <h2>New Client</h2>
                <p>Start your first tax return with us. We'll guide you step-by-step.</p>
                <button class="btn btn-primary">Start Intake</button>
            </div>
            <div class="portal-card" onclick="startForm('existing')">
                <h2>Existing Client</h2>
                <p>Welcome back! Let's get caught up and update your information.</p>
                <button class="btn btn-primary">Update Info</button>
            </div>
        </div>
        <div style="text-align: center; margin-top: 3rem;">
            <a href="admin.html"
               style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; opacity: 0.5;">Admin
                Dashboard</a>
        </div>
    </div>

    <!-- Form Container -->
    <div id="form-container" class="form-container">
        <div class="progress-bar">
            <div id="progress-fill" class="progress-fill"></div>
        </div>

        <form id="crs-client-form">
            <input type="hidden" name="action" value="crs_submit_client">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('crs_nonce'); ?>">
            <input type="hidden" name="client_category" id="client_category_field">
            <!-- Part 1: Personal Info -->
            <div id="step-1" class="form-step active">
                <h3 class="section-title">Part 1: Personal Information</h3>
                <div id="existing-welcome" style="display: none; margin-bottom: 2rem;">
                    <p style="color: var(--accent); font-weight: 600;">Welcome back, let's get caught up!</p>
                </div>

                <div class="input-grid">
                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="fullName" required placeholder="John Doe">
                    </div>
                    <div class="input-group" id="ssn-group">
                        <label>Social Security Number</label>
                        <input type="password" name="ssn" required placeholder="XXX-XX-XXXX">
                    </div>
                    <div class="input-group" id="dob-group">
                        <label>Date of Birth</label>
                        <input type="text" name="dob" required placeholder="MM/DD/YYYY" pattern="\d{2}/\d{2}/\d{4}">
                    </div>
                    <div class="input-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation" required >
                    </div>
                    <div class="input-group">
                        <label>Cell Phone</label>
                        <input type="tel" name="phone" required placeholder="(469) 939-3039">
                    </div>
                    <div class="input-group">
                        <label>Email</label>
                        <input type="email" name="email" required placeholder="john@example.com">
                    </div>
                </div>

                <div class="input-group" style="margin-bottom: 2rem;">
                    <label>Full Address</label>
                    <textarea name="address" required rows="2" placeholder="Street, City, State, ZIP"></textarea>
                </div>

                <div class="input-grid">
                    <div class="input-group">
                        <label>Bank Account Number (Optional)</label>
                        <input type="password" name="bankAccount" placeholder="For Direct Deposit">
                    </div>
                    <div class="input-group">
                        <label>Bank Routing Number (Optional)</label>
                        <input type="password" name="bankRouting" placeholder="9 Digits">
                    </div>
                </div>

                <h4 style="margin: 2rem 0 1rem; color: var(--text-muted);">Filing Status</h4>
                <div class="bubble-grid">
                    <label class="bubble-option">
                        <input type="radio" name="filingStatus" value="single" required
                               onchange="handleStatusChange(this)">
                        <div>
                            <strong>Single</strong>
                        </div>
                    </label>
                    <label class="bubble-option">
                        <input type="radio" name="filingStatus" value="Head of Household" onchange="handleStatusChange(this)">
                        <div>
                            <strong>Head of Household</strong>
                            <p style="font-size: 0.8rem; opacity: 0.7;">To qualify, you must be unmarried (or
                                separated), have paid more than half the cost of keeping up your home. Note: only
                                one taxpayer may claim head of household per home.</p>
                        </div>
                    </label>
                    <label class="bubble-option">
                        <input type="radio" name="filingStatus" value="married_joint"
                               onchange="handleStatusChange(this)">
                        <div>
                            <strong>Filing Married with Spouse</strong>
                        </div>
                    </label>
                    <label class="bubble-option">
                        <input type="radio" name="filingStatus" value="married_sep"
                               onchange="handleStatusChange(this)">
                        <div>
                            <strong>Filing Married separately from Spouse</strong>
                        </div>
                    </label>
                </div>

                <!-- Spouse Sub-form (Hidden) -->
                <div id="spouse-info"
                     style="display: none; margin-top: 2rem; padding: 2rem; border: 1px dashed var(--border); border-radius: 20px;">
                    <h4 style="margin-bottom: 1.5rem; color: var(--accent);">Spouse Information</h4>
                    <div class="input-grid">
                        <div class="input-group">
                            <label>Spouse Full Name</label>
                            <input type="text" name="spouseName">
                        </div>
                        <div class="input-group">
                            <label>Spouse SSN</label>
                            <input type="password" name="spouseSSN">
                        </div>
                        <div class="input-group">
                            <label>Spouse DOB</label>
                            <input type="text" name="spouseDOB" placeholder="MM/DD/YYYY" pattern="\d{2}/\d{2}/\d{4}">
                        </div>
                        <div class="input-group">
                            <label>Spouse Email</label>
                            <input type="email" name="spouseEmail">
                        </div>
                        <div class="input-group">
                            <label>Spouse Occupation</label>
                            <input type="text" name="spouseOccupation">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 3rem; display: flex; justify-content: flex-end;">
                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next: Part 2 &rarr;</button>
                </div>
            </div>

            <!-- Part 2: Questionnaire -->
            <div id="step-2" class="form-step">
                <h3 class="section-title">Part 2: Questionnaire</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">Please answer all questions. Multiple
                    choice bubbles are required.</p>

                <div class="questionnaire-list" id="questions-list">
                    <!-- Questions will be injected via JS to keep HTML clean -->
                </div>

                <div style="margin-top: 3rem; display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-outline" onclick="prevStep(1)">&larr; Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next: Part 3 &rarr;</button>
                </div>
            </div>

            <!-- Part 3: Dependents -->
            <div id="step-3" class="form-step">
                <h3 class="section-title">Part 3: Dependents</h3>

                <div class="input-group" style="margin-bottom: 2.5rem;">
                    <label style="font-size: 1.1rem; color: #fff; margin-bottom: 1rem; display: block;">Are you
                        claiming dependents?</label>
                    <div class="bubble-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <label class="bubble-option" id="dep-yes-label">
                            <input type="radio" name="claimingDependents" value="yes"
                                   onchange="toggleDependents(true)" required>
                            <strong>Yes</strong>
                        </label>
                        <label class="bubble-option selected" id="dep-no-label">
                            <input type="radio" name="claimingDependents" value="no"
                                   onchange="toggleDependents(false)" checked>
                            <strong>No</strong>
                        </label>
                    </div>
                </div>

                <div id="dependents-form"
                     style="display: none; border-top: 1px solid var(--border); padding-top: 2rem; margin-top: 2rem;">
                    <div id="dependents-list">
                        <!-- Dependent cards added here -->
                    </div>
                    <button type="button" class="btn btn-outline" id="add-dependent-btn" onclick="addDependent()"
                            style="width: 100%; margin-top: 1rem; border-style: dashed;">
                        + Add Another Dependent (Max 6)
                    </button>
                </div>

                <div style="margin-top: 3rem; display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-outline" onclick="prevStep(2)">&larr; Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next: Part 4 &rarr;</button>
                </div>
            </div>

            <!-- Part 4: Certification & Upload -->
            <div id="step-4" class="form-step">
                <h3 class="section-title">Part 4: Certification</h3>

                <div
                        style="background: rgb(255 255 255 / 53%); border: 1px solid var(--border); padding: 2rem; border-radius: 20px; margin-bottom: 2rem;">
                    <label style="display: flex; gap: 1rem; cursor: pointer;">
                        <input type="checkbox" name="certify" required
                               style="width: 20px; height: 20px; margin-top: 0.2rem;">
                        <span>I certify that the information provided is accurate and complete to the best of my
                                    knowledge. I understand that providing false information may result in penalties or
                                    legal consequences.</span>
                    </label>
                </div>

                <!-- File Upload or Link Section -->
                <div style="background: rgb(255 255 255 / 53%); border: 1px solid var(--border); padding: 2rem; border-radius: 20px; margin-bottom: 2rem;">
                    <h4 style="margin-bottom: 1.5rem; color: var(--accent);">Document Upload or Link</h4>

                    <div class="input-group" style="margin-bottom: 1.5rem;">
                        <label style="display: flex; gap: 1rem; cursor: pointer;">
                            <input type="checkbox" id="use_link_checkbox">
                            <span>Provide a link instead of uploading a file</span>
                        </label>
                    </div>

                    <div id="file_upload_section">
                        <div class="input-group">
                            <label>Upload Documents (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG - Max 5MB each)</label>
                            <input type="file" name="client_document[]" id="client_document" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" capture="environment" multiple style="padding: 0.5rem; border: 1px solid var(--border); border-radius: 10px; width: 100%;">
                        </div>
                    </div>

                    <div id="link_section" style="display: none;">
                        <div class="input-group">
                            <label>Document Links (one per line)</label>
                            <textarea name="client_document_link" id="client_document_link" rows="3" placeholder="Paste your document links here, one per line..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 10px;"></textarea>
                        </div>
                    </div>
                </div>



                <div style="margin-top: 3rem; display: flex; justify-content: space-between;">
                    <button type="button" class="btn btn-outline" onclick="prevStep(3)">&larr; Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(5)">Review & Perks
                        &rarr;</button>
                </div>
            </div>

            <!-- Part 5: Perks & Submit -->
            <div id="step-5" class="form-step">
                <h3 class="section-title">Part 5: Client Perks</h3>

                <div class="input-grid">
                    <div class="portal-card" style="padding: 1.5rem;">
                        <h4 style="color: var(--accent);">Monthly Raffle</h4>
                        <p style="font-size: 0.9rem;">Upon filing your taxes, you will be entered for a chance to win a <strong>40-inch TV!</strong> Increase your chances of winning: Leave a review on google or facebook for a second entry.</p>
                    </div>
                    <div class="portal-card" style="padding: 1.5rem;">
                        <h4 style="color: var(--accent);">Bonus Reward</h4>
                        <p style="font-size: 0.9rem;">Earn a <strong>$10 Starbucks Card</strong> for tagging us or checking in on social media.</p>
                    </div>
                    <div class="portal-card" style="padding: 1.5rem;">
                        <h4 style="color: var(--accent);">Referral Program</h4>
                        <p style="font-size: 0.9rem;">Refer a friend and get <strong>$50</strong> as a thank you!
                        </p>
                    </div>
                </div>

                <div style="margin: 3rem 0; text-align: center;">
                    <p style="color: var(--text-muted); margin-bottom: 2rem;">Please review your information before
                        printing and submitting.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center;">
                        <button type="button" class="btn btn-outline" onclick="generateReport()">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                        d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z" />
                            </svg>
                            Print Report
                        </button>
                        <button type="submit" class="btn btn-primary" style="padding: 1rem 4rem;">
                            Submit Intake
                        </button>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-start;">
                    <button type="button" class="btn btn-outline" onclick="prevStep(4)">&larr; Previous</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="crs-response-message"></div>
