let currentClientType = 'new';
let currentStep = 1;
let dependentCount = 0;

const questions = [
    { id: 'selfEmployed', text: 'Are you self-employed or a contractor (1099)?' },
    { id: 'overtime', text: 'Did you work overtime last year?' },
    { id: 'collegeTuition', text: 'Did you pay college tuition last year?' },
    { id: 'studentLoans', text: 'Did you make student loan payments?' },
    { id: 'ownHome', text: 'Do you own your own home?' },
    { id: 'newVehicle', text: 'Did you purchase a brand new vehicle last year?' },
    { id: 'socialSecurity', text: 'Do you receive social security benefits?' },
    { id: 'retirementWithdraw', text: 'Did you withdraw money from a retirement account (401K, IRA, Ect)?' },
    { id: 'sellExchange', text: 'Did you sell or exchange:', options: ['Stock', 'Cryptocurrency', 'None'], name: 'sellExchangeType' },
    { id: 'unemployment', text: 'Did you receive unemployment or leave of absence pay?', options: ['Unemployment', 'Leave of absence', 'None'], name: 'payType' },
    { id: 'healthInsurance', text: 'Who provided your health insurance?', options: ['Work', 'Parents', 'Marketplace', 'Uninsured'], name: 'insuranceProvider' }
];

function startForm(type) {
    currentClientType = type;

    document.getElementById('client_category_field').value =
        type === 'new' ? 'New Client' : 'Existing Client';

    document.getElementById('selection-view').style.display = 'none';
    // document.getElementById('welcome-header').style.display = 'none';
    document.getElementById('form-container').style.display = 'block';

    if (type === 'existing') {
        document.getElementById('existing-welcome').style.display = 'block';

        // Hide SSN and DOB for existing clients
        document.getElementById('ssn-group').style.display = 'none';
        document.querySelector('input[name="ssn"]').required = false;
        document.getElementById('dob-group').style.display = 'none';
        document.querySelector('input[name="dob"]').required = false;

        // Force a balanced 2-column layout for the 4 remaining fields
        const mainGrid = document.querySelector('#step-1 .input-grid');
        if (mainGrid) mainGrid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(400px, 1fr))';

        // Swap out SSN for Notes in any existing dependent cards
        document.querySelectorAll('.ssn-group-dep').forEach(el => el.style.display = 'none');
        document.querySelectorAll('input[name^="depSSN_"]').forEach(el => el.required = false);
        document.querySelectorAll('.notes-group-dep').forEach(el => el.style.display = '');

        // Hide referral for existing clients
        const refGroup = document.getElementById('referral-group');
        if (refGroup) refGroup.style.display = 'none';

        // Make occupation, cell, email and address optional
        ['occupation', 'phone', 'email'].forEach(f => {
            const el = document.querySelector(`input[name="${f}"]`);
            if (el) {
                el.required = false;
                const label = el.previousElementSibling;
                if (label && !label.innerHTML.includes('(Optional)')) {
                    label.innerHTML += ' <span class="optional-tag" style="opacity:0.7; font-size:0.9em;">(Optional)</span>';
                }
            }
        });
        const addr = document.querySelector('textarea[name="address"]');
        if (addr) {
            addr.required = false;
            const label = addr.previousElementSibling;
            if (label && !label.innerHTML.includes('(Optional)')) {
                label.innerHTML += ' <span class="optional-tag" style="opacity:0.7; font-size:0.9em;">(Optional)</span>';
            }
        }
    } else {
        document.getElementById('existing-welcome').style.display = 'none';

        // Restore SSN and DOB for new clients
        document.getElementById('ssn-group').style.display = '';
        document.querySelector('input[name="ssn"]').required = true;
        document.getElementById('dob-group').style.display = '';
        document.querySelector('input[name="dob"]').required = true;

        // Restore default layout
        const mainGrid = document.querySelector('#step-1 .input-grid');
        if (mainGrid) mainGrid.style.gridTemplateColumns = '';

        // Restore SSN and hide Notes in any existing dependent cards
        document.querySelectorAll('.ssn-group-dep').forEach(el => el.style.display = '');
        document.querySelectorAll('input[name^="depSSN_"]').forEach(el => el.required = true);
        document.querySelectorAll('.notes-group-dep').forEach(el => el.style.display = 'none');

        // Restore referral for new clients
        const refGroup = document.getElementById('referral-group');
        if (refGroup) refGroup.style.display = '';

        // Restore required fields
        ['occupation', 'phone', 'email'].forEach(f => {
            const el = document.querySelector(`input[name="${f}"]`);
            if (el) {
                el.required = true;
                const label = el.previousElementSibling;
                if (label) {
                    const tag = label.querySelector('.optional-tag');
                    if (tag) tag.remove();
                }
            }
        });
        const addr = document.querySelector('textarea[name="address"]');
        if (addr) {
            addr.required = true;
            const label = addr.previousElementSibling;
            if (label) {
                const tag = label.querySelector('.optional-tag');
                if (tag) tag.remove();
            }
        }
    }

    initQuestionnaire();
    updateProgress();
}

function initQuestionnaire() {
    const list = document.getElementById('questions-list');
    list.innerHTML = '';

    questions.forEach(q => {
        const div = document.createElement('div');
        div.className = 'input-group';
        div.style.marginBottom = '2rem';

        const label = document.createElement('label');
        label.style.display = 'block';
        label.style.marginBottom = '1rem';
        label.style.fontSize = '1.1rem';
        label.style.color = '#fff';
        label.innerText = q.text;
        div.appendChild(label);

        const grid = document.createElement('div');
        grid.className = 'bubble-grid';
        if (q.options) {
            grid.style.gridTemplateColumns = `repeat(${q.options.length}, 1fr)`;
            q.options.forEach(opt => {
                grid.appendChild(createBubble(q.name || q.id, opt, opt));
            });
        } else {
            grid.style.gridTemplateColumns = '1fr 1fr';
            grid.appendChild(createBubble(q.id, 'Yes', 'yes'));
            grid.appendChild(createBubble(q.id, 'No', 'no'));
        }
        div.appendChild(grid);
        list.appendChild(div);
    });
}

function createBubble(name, labelText, value) {
    const label = document.createElement('label');
    label.className = 'bubble-option';
    label.innerHTML = `
        <input type="radio" name="${name}" value="${value}" required>
        <strong>${labelText}</strong>
    `;
    return label;
}

function nextStep(step) {
    // Basic validation for current step
    const currentStepFields = document.getElementById(`step-${currentStep}`).querySelectorAll('[required]');
    let valid = true;
    currentStepFields.forEach(f => {
        if (!f.checkValidity()) {
            f.reportValidity();
            valid = false;
        }
    });

    if (!valid) return;

    document.getElementById(`step-${currentStep}`).classList.remove('active');
    currentStep = step;
    document.getElementById(`step-${currentStep}`).classList.add('active');
    updateProgress();
    window.scrollTo(0, 0);
}

function prevStep(step) {
    document.getElementById(`step-${currentStep}`).classList.remove('active');
    currentStep = step;
    document.getElementById(`step-${currentStep}`).classList.add('active');
    updateProgress();
    window.scrollTo(0, 0);
}

function updateProgress() {
    const fill = document.getElementById('progress-fill');
    fill.style.width = `${(currentStep / 5) * 100}%`;
}

function toggleDependents(show) {
    const form = document.getElementById('dependents-form');
    if (!form) return;

    // 1. If showing and no dependents yet, add the first one FIRST
    if (show && dependentCount === 0) {
        addDependent();
    }

    // 2. Now show/hide the container
    form.style.display = show ? 'block' : 'none';

    // 3. Now loop through ALL fields (including any just added) to set the correct state
    const fields = form.querySelectorAll('input, select, textarea');
    fields.forEach(i => {
        if (show) {
            i.disabled = false;

            // Set required intelligently based on field name
            if (i.name.startsWith('depNotes_') ||
                (i.name.startsWith('depSSN_') && currentClientType === 'existing')) {
                i.required = false;
            } else if (i.type !== 'radio' && i.type !== 'checkbox' && i.tagName !== 'TEXTAREA') {
                // By default make other text inputs/selects required
                i.required = true;
            }
        } else {
            i.required = false;
            i.disabled = true;
            i.setCustomValidity(''); // Clear any validation errors
        }
    });
}

// Ensure the same robust logic applies to the spouse info section
function handleStatusChange(radio) {
    const spouseInfo = document.getElementById('spouse-info');
    const isMarried = (radio.value === 'married_joint' || radio.value === 'married_sep');

    spouseInfo.style.display = isMarried ? 'block' : 'none';
    spouseInfo.querySelectorAll('input').forEach(i => {
        i.required = isMarried;
        i.disabled = !isMarried;
        if (!isMarried) i.setCustomValidity('');
    });
}

// Add event listener to handle bubble selection styling globally
document.addEventListener('change', function (e) {
    if (e.target.type === 'radio') {
        const name = e.target.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(input => {
            const container = input.closest('.bubble-option');
            if (container) {
                if (input.checked) {
                    container.classList.add('selected');
                } else {
                    container.classList.remove('selected');
                }
            }
        });
    }
});



function addDependent() {
    if (dependentCount >= 6) return;
    dependentCount++;

    const list = document.getElementById('dependents-list');
    const card = document.createElement('div');
    card.className = 'portal-card';
    card.style.padding = '2rem';
    card.style.marginBottom = '1.5rem';
    card.style.textAlign = 'left';

    // Check if the form is currently visible to set initial state of new fields
    const isVisible = document.getElementById('dependents-form').style.display === 'block';

    card.innerHTML = `
        <h4 style="margin-bottom: 1.5rem; color: var(--accent); display: flex; justify-content: space-between;">
            Dependent #${dependentCount}
        </h4>
        <div class="input-grid">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="depName_${dependentCount}" required ${!isVisible ? 'disabled' : ''}>
            </div>
            <div class="input-group ssn-group-dep" style="display: ${currentClientType === 'existing' ? 'none' : ''};">
                <label>Social Security</label>
                <input type="text" name="depSSN_${dependentCount}" ${currentClientType === 'existing' ? '' : 'required'} placeholder="XXX-XX-XXXX" ${!isVisible ? 'disabled' : ''}>
            </div>
            <div class="input-group notes-group-dep" style="display: ${currentClientType === 'existing' ? '' : 'none'}; grid-column: 1 / -1;">
                <label>Please share any additional notes, updates, or special requests here <span class="optional-tag" style="opacity:0.7; font-size:0.9em;">(Optional)</span></label>
                <textarea name="depNotes_${dependentCount}" rows="2" ${!isVisible ? 'disabled' : ''}></textarea>
            </div>
            <div class="input-group">
                <label>Date of Birth</label>
                <input type="text" name="depDOB_${dependentCount}" required ${!isVisible ? 'disabled' : ''} placeholder="MM/DD/YYYY" pattern="\\d{2}/\\d{2}/\\d{4}">
            </div>
            <div class="input-group">
                <label>Relationship</label>
                <select name="depRel_${dependentCount}" required ${!isVisible ? 'disabled' : ''}>
                    <option value="">Select...</option>
                    <option>Son</option>
                    <option>Daughter</option>
                    <option>Stepchild</option>
                    <option>Sibling</option>
                    <option>Stepsibling</option>
                    <option>Niece/Nephew</option>
                    <option>Grandchild</option>
                    <option>Parent</option>
                </select>
            </div>
        </div>
        <div class="input-grid" style="margin-top: 1rem;">
            <div class="input-group">
                <label>Did the dependent live with you for more than 6 months?</label>
                <div style="display: flex; gap: 1rem;">
                    <label class="bubble-option" style="flex: 1;"><input type="radio" name="depLive_${dependentCount}" value="yes" required ${!isVisible ? 'disabled' : ''}> Yes</label>
                    <label class="bubble-option" style="flex: 1;"><input type="radio" name="depLive_${dependentCount}" value="no" ${!isVisible ? 'disabled' : ''}> No</label>
                </div>
            </div>
            <div class="input-group">
                <label>Did you pay for childcare for this dependent last year?</label>
                <div style="display: flex; gap: 1rem;">
                    <label class="bubble-option" style="flex: 1;"><input type="radio" name="depCare_${dependentCount}" value="yes" required ${!isVisible ? 'disabled' : ''}> Yes</label>
                    <label class="bubble-option" style="flex: 1;"><input type="radio" name="depCare_${dependentCount}" value="no" ${!isVisible ? 'disabled' : ''}> No</label>
                </div>
            </div>
        </div>
    `;
    list.appendChild(card);

    if (dependentCount >= 6) {
        document.getElementById('add-dependent-btn').style.display = 'none';
    }
}

document.getElementById('crs-client-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch(crs_ajax_object.ajax_url, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to Thank You page
                window.location.href = crs_ajax_object.thank_you_url;
            } else {
                alert('Something went wrong.');
            }
        })
        .catch(error => {
            alert('AJAX error occurred.');
        });
});

// Generate PDF Reporto for client
function formatLabel(key) {

    const labels = {
        fullName: 'Full Name',
        ssn: 'Social Security Number',
        dob: 'Date of Birth',
        occupation: 'Occupation',
        phone: 'Phone',
        email: 'Email',
        address: 'Address',
        bankAccount: 'Bank Account',
        bankRouting: 'Bank Routing',
        referral: 'Referred By',
        filingStatus: 'Filing Status',
        selfEmployed: 'Self Employed / 1099',
        overtime: 'Worked Overtime',
        collegeTuition: 'College Tuition',
        studentLoans: 'Student Loans',
        ownHome: 'Own Home',
        newVehicle: 'Purchased New Vehicle',
        socialSecurity: 'Receiving Social Security',
        retirementWithdraw: 'Retirement Withdrawal',
        sellExchangeType: 'Sold / Exchanged',
        payType: 'Unemployment / Leave',
        insuranceProvider: 'Health Insurance Provider',
        claimingDependents: 'Claiming Dependents'
    };

    return labels[key] || key;
}

function generateReport() {

    const form = document.getElementById('crs-client-form');
    const formData = new FormData(form);

    const data = {};

    formData.forEach((value, key) => {
        data[key] = value;
    });

    let reportHTML = `
        <html>
        <head>
            <title>Client Intake Report</title>
            <style>
                body { font-family: Arial; padding: 40px; }
                h1 { text-align: center; margin-bottom: 40px; }
                h2 { margin-top: 40px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                th { background: #f2f2f2; width: 30%; }
            </style>
        </head>
        <body>
            <h1>Client Intake Report</h1>
    `;

    // =========================
    // Personal Information
    // =========================
    const personalFields = [
        'fullName', 'ssn', 'dob', 'occupation', 'phone', 'email', 'address',
        'bankAccount', 'bankRouting', 'referral', 'filingStatus'
    ];

    reportHTML += `<h2>Personal Information</h2><table>`;

    personalFields.forEach(key => {
        if (data[key]) {
            reportHTML += `
                <tr>
                    <th>${formatLabel(key)}</th>
                    <td>${data[key]}</td>
                </tr>
            `;
        }
    });


    reportHTML += `</table>`;


    // =========================
    // Questionnaire
    // =========================
    const questionnaireFields = [
        'selfEmployed', 'overtime', 'collegeTuition', 'studentLoans',
        'ownHome', 'newVehicle', 'socialSecurity', 'retirementWithdraw',
        'sellExchangeType', 'payType', 'insuranceProvider',
        'claimingDependents'
    ];

    reportHTML += `<h2>Questionnaire</h2><table>`;

    questionnaireFields.forEach(key => {
        if (data[key]) {
            reportHTML += `
                <tr>
                    <th>${formatLabel(key)}</th>
                    <td>${data[key]}</td>
                </tr>
            `;
        }
    });

    reportHTML += `</table>`;


    // =========================
    // Dependents
    // =========================
    let hasDependents = false;

    for (let i = 1; i <= 6; i++) {

        if (data[`depName_${i}`]) {

            if (!hasDependents) {
                reportHTML += `<h2>Dependents</h2>`;
                hasDependents = true;
            }

            reportHTML += `
                <h3>Dependent ${i}</h3>
                <table>
                    <tr><th>Name</th><td>${data[`depName_${i}`] || ''}</td></tr>
                    ${data[`depSSN_${i}`] ? `<tr><th>SSN</th><td>${data[`depSSN_${i}`]}</td></tr>` : ''}
                    <tr><th>Date of Birth</th><td>${data[`depDOB_${i}`] || ''}</td></tr>
                    <tr><th>Relationship</th><td>${data[`depRel_${i}`] || ''}</td></tr>
                    ${data[`depNotes_${i}`] ? `<tr><th>Notes / Updates</th><td>${data[`depNotes_${i}`]}</td></tr>` : ''}
                    <tr><th>Lived > 6 months</th><td>${data[`depLive_${i}`] || ''}</td></tr>
                    <tr><th>Childcare Paid</th><td>${data[`depCare_${i}`] || ''}</td></tr>
                </table>
            `;
        }
    }





    reportHTML += `
        <script>
            window.onload = function() {
                window.print();
            }
        <\/script>
    `;

    reportHTML += `</body></html>`;

    const reportWindow = window.open('', '_blank');
    reportWindow.document.write(reportHTML);
    reportWindow.document.close();
}


