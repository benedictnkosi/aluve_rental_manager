$(document).ready(function () {
    sessionStorage.setItem("inspection_guid", "0");
    document.getElementById('saveButton').addEventListener('click', function () {
        saveInspection("active");
    });

    getInspectionDetails();

    initializeImgUploader();
});

let getURLParameter= (name) =>{
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    return urlParams.get(name);
}

function initializeImgUploader() {
    var imgWrap = "";
    var imgArray = [];

    $('.upload__inputfile').each(function () {
        $(this).on('change', function (e) {
            imgWrap = $(this).closest('.upload__box').find('.upload__img-wrap');
            var maxLength = $(this).attr('data-max_length');

            var files = e.target.files;
            var filesArr = Array.prototype.slice.call(files);
            var iterator = 0;
            filesArr.forEach(function (f, index) {

                if (!f.type.match('image.*')) {
                    return;
                }

                if (imgArray.length > maxLength) {
                    return false
                } else {
                    var len = 0;
                    for (var i = 0; i < imgArray.length; i++) {
                        if (imgArray[i] !== undefined) {
                            len++;
                        }
                    }
                    if (len > maxLength) {
                        return false;
                    } else {
                        imgArray.push(f);

                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var html = "<div class='upload__img-box'><div style='background-image: url(" + e.target.result + ")' data-number='" + $(".upload__img-close").length + "' data-file='" + f.name + "' class='img-bg'><div class='upload__img-close'></div></div></div>";
                            imgWrap.append(html);
                            iterator++;
                        }
                        reader.readAsDataURL(f);
                        uploadInspectionImage(f);
                    }
                }
            });
        });
    });

    $('body').on('click', ".upload__img-close", function (e) {
        var file = $(this).parent().data("file");
        for (var i = 0; i < imgArray.length; i++) {
            if (imgArray[i].name === file) {
                imgArray.splice(i, 1);
                break;
            }
        }
        $(this).parent().parent().remove();
    });
}

function uploadInspectionImage(file_data) {
    let url = "/api/inspection/upload/image/";
    const uid = getURLParameter("guid");
    const form_data = new FormData();
    form_data.append("file", file_data);
    form_data.append("inspection_guid", sessionStorage.getItem("inspection_guid"));

    if (file_data === undefined) {
        showToast("Error: Please upload file")
        return;
    }

    const fileSize =file_data.size;
    const fileMb = fileSize / 1024 ** 2;
    if (fileMb >= 10) {
        showToast("Error: Please upload files less than 10mb");
        return;
    }

    $.ajax({
        url: url,
        type: "POST",
        data: form_data,
        dataType: 'script',
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            const jsonObj = JSON.parse(response);
            showToast(jsonObj.result_message);
            if(jsonObj.alldocs_uploaded === true){
                $('#text-message').removeClass("display-none");
                $('#regForm').addClass("display-none");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast(errorThrown);
        }
    });
}

let saveInspection = (status) => {
    const checklistData = {
        bedroomChecklist: generateBedroomChecklistData(),
        kitchenChecklist: generateChecklistData('kitchen'),
        livingRoomChecklist: generateChecklistData('livingRoom'),
        bathroomChecklist: generateBathroomChecklistData()
    };

    let url = "/api/lease/create/inspection";
    const data = {
        inspection:JSON.stringify(checklistData),
        lease_guid: sessionStorage.getItem("lease_guid"),
        inspection_guid: sessionStorage.getItem("inspection_guid"),
        status: status
    };

    $.ajax({
        url: url,
        type: "post",
        data: data,
        success: function (response) {
            sessionStorage.setItem("inspection_guid",response.guid);
            showToast(response.result_message)
        }
    });

    console.log(JSON.stringify(checklistData, null, 2));
}

let getInspectionDetails = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const guid = urlParams.get('guid');
    let url = "/no_auth/lease/" + guid
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            sessionStorage.setItem("bedrooms", data.bedrooms);
            sessionStorage.setItem("bathrooms", data.bathrooms);
            sessionStorage.setItem("lease_guid", guid
            );
            generateBedroomChecklist(parseInt(data.bedrooms));
            generateBathroomChecklist(parseInt(data.bathrooms));
            $("#property-name").html(data.property);
            $("#unit-name").html(data.unit_name);
            //save empty inspection so you have id for uploading images
            saveInspection("new");
        },
        error: function (xhr) {

        }
    });
}

function generateBathroomChecklist(numBedrooms) {
    const bathroomChecklist = document.getElementById('bathroomChecklist');
    bathroomChecklist.innerHTML = '';

    for (let i = 1; i <= numBedrooms; i++) {
        const bathroomDiv = document.createElement('div');
        bathroomDiv.classList.add('form-check-columns');
        bathroomDiv.classList.add('mt-3');

        const bathroomHeader = createRoomHeader(i, "Bathroom");
        const plumbingCheck = createCheckField(`plumbingCheck${i}b`, 'Plumbing');
        const vanityCheck = createCheckField(`vanityCheck${i}b`, 'Vanity');
        const wallsCheck = createCheckField(`wallsCheck${i}b`, 'Walls');
        const doorsCheck = createCheckField(`doorsCheck${i}b`, 'Doors');
        const windowsCheck = createCheckField(`windowsCheck${i}b`, 'Windows');
        const lightingCheck = createCheckField(`lightingCheck${i}b`, 'Lighting');
        const floorsCheck = createCheckField(`floorsCheck${i}b`, 'Floors');
        const toiletCheck = createCheckField(`toiletCheck${i}b`, 'Toilet and Tissue Holder');
        const showerTubCheck = createCheckField(`showerTubCheck${i}b`, 'Shower and Tub');
        const medicineCabinetCheck = createCheckField(`medicineCabinetCheck${i}b`, 'Medicine Cabinet');

        bathroomDiv.appendChild(bathroomHeader);
        bathroomDiv.appendChild(plumbingCheck);
        bathroomDiv.appendChild(vanityCheck);
        bathroomDiv.appendChild(wallsCheck);
        bathroomDiv.appendChild(doorsCheck);
        bathroomDiv.appendChild(windowsCheck);
        bathroomDiv.appendChild(lightingCheck);
        bathroomDiv.appendChild(floorsCheck);
        bathroomDiv.appendChild(toiletCheck);
        bathroomDiv.appendChild(showerTubCheck);
        bathroomDiv.appendChild(medicineCabinetCheck);

        bathroomChecklist.appendChild(bathroomDiv);
    }
}

function generateBedroomChecklist(numBedrooms) {
    const bedroomChecklist = document.getElementById('bedroomChecklist');
    bedroomChecklist.innerHTML = '';

    for (let i = 1; i <= numBedrooms; i++) {
        const bedroomDiv = document.createElement('div');
        bedroomDiv.classList.add('form-check-columns');

        const bedroomHeader = createRoomHeader(i, "Bedroom");
        const wardrobesCheck = createCheckField(`wardrobeCheck${i}a`, 'Wardrobes');
        const wallsCheck = createCheckField(`wallsCheck${i}a`, 'Walls');
        const doorsCheck = createCheckField(`doorsCheck${i}a`, 'Doors');
        const windowsCheck = createCheckField(`windowsCheck${i}a`, 'Windows');
        const lightingCheck = createCheckField(`lightingCheck${i}a`, 'Lighting');
        const floorsCheck = createCheckField(`floorsCheck${i}a`, 'Floors');

        bedroomDiv.appendChild(bedroomHeader);
        bedroomDiv.appendChild(wardrobesCheck);
        bedroomDiv.appendChild(wallsCheck);
        bedroomDiv.appendChild(doorsCheck);
        bedroomDiv.appendChild(windowsCheck);
        bedroomDiv.appendChild(lightingCheck);
        bedroomDiv.appendChild(floorsCheck);

        bedroomChecklist.appendChild(bedroomDiv);
    }
}

function createRoomHeader(number, roomName) {
    const roomHeader = document.createElement('h2');
    if(number === 1){
        roomHeader.textContent = `${roomName}`;
    }else{
        roomHeader.textContent = `${roomName} ${number}`;
    }
    return roomHeader;
}

function createCheckField(id, label) {
    const checkDiv = document.createElement('div');
    checkDiv.classList.add('form-check');

    const checkbox = document.createElement('input');
    checkbox.classList.add('form-check-input');
    checkbox.type = 'checkbox';
    checkbox.id = id;

    const checkboxLabel = document.createElement('label');
    checkboxLabel.classList.add('form-check-label');
    checkboxLabel.htmlFor = id;
    checkboxLabel.textContent = label;

    const comment = createCommentsField(`${id}_comments`);

    checkDiv.appendChild(checkbox);
    checkDiv.appendChild(checkboxLabel);
    checkDiv.appendChild(comment);

    return checkDiv;
}

function createCommentsField(id) {
    const commentsInput = document.createElement('input');
    commentsInput.classList.add('form-control', 'form-control-comments');
    commentsInput.type = 'text';
    commentsInput.id = id;
    commentsInput.placeholder = 'Additional Comments';

    return commentsInput;
}

function generateBedroomChecklistData() {
    const numBedrooms = parseInt(sessionStorage.getItem("bedrooms"));
    const bedroomChecklistData = [];

    for (let i = 1; i <= numBedrooms; i++) {
        const bedroomData = {
            bedroomNumber: i,
            wardrobe: document.getElementById(`wardrobeCheck${i}a`).checked,
            walls: document.getElementById(`wallsCheck${i}a`).checked,
            doors: document.getElementById(`doorsCheck${i}a`).checked,
            windows: document.getElementById(`windowsCheck${i}a`).checked,
            lighting: document.getElementById(`lightingCheck${i}a`).checked,
            floors: document.getElementById(`floorsCheck${i}a`).checked,


            wardrobeComments: document.getElementById(`wardrobeCheck${i}a_comments`).value,
            wallsComments: document.getElementById(`wallsCheck${i}a_comments`).value,
            doorsComments: document.getElementById(`doorsCheck${i}a_comments`).value,
            windowsComments: document.getElementById(`windowsCheck${i}a_comments`).value,
            lightingComments: document.getElementById(`lightingCheck${i}a_comments`).value,
            floorsComments: document.getElementById(`floorsCheck${i}a_comments`).value
        };

        bedroomChecklistData.push(bedroomData);
    }

    return bedroomChecklistData;
}

function generateBathroomChecklistData() {
    const numBathrooms = parseInt(sessionStorage.getItem("bathrooms"));
    const bathroomChecklistData = [];

    for (let i = 1; i <= numBathrooms; i++) {
        const bathroomData = {
            bathroomNumber: i,
            vanity: document.getElementById(`vanityCheck${i}b`).checked,
            plumbing: document.getElementById(`plumbingCheck${i}b`).checked,
            walls: document.getElementById(`wallsCheck${i}b`).checked,
            doors: document.getElementById(`doorsCheck${i}b`).checked,
            windows: document.getElementById(`windowsCheck${i}b`).checked,
            lighting: document.getElementById(`lightingCheck${i}b`).checked,
            floors: document.getElementById(`floorsCheck${i}b`).checked,
            toilet: document.getElementById(`toiletCheck${i}b`).checked,
            showerTub: document.getElementById(`showerTubCheck${i}b`).checked,
            medicineCabinet: document.getElementById(`medicineCabinetCheck${i}b`).checked,

            plumbingComments: document.getElementById(`plumbingCheck${i}b_comments`).value,
            vanityComments: document.getElementById(`vanityCheck${i}b_comments`).value,
            wallsComments: document.getElementById(`wallsCheck${i}b_comments`).value,
            doorsComments: document.getElementById(`doorsCheck${i}b_comments`).value,
            windowsComments: document.getElementById(`windowsCheck${i}b_comments`).value,
            lightingComments: document.getElementById(`lightingCheck${i}b_comments`).value,
            floorsComments: document.getElementById(`floorsCheck${i}b_comments`).value,
            toiletComments: document.getElementById(`toiletCheck${i}b_comments`).value,
            showerComments: document.getElementById(`showerTubCheck${i}b_comments`).value,
            medicineCabinetComments: document.getElementById(`medicineCabinetCheck${i}b_comments`).value

        };

        bathroomChecklistData.push(bathroomData);
    }

    return bathroomChecklistData;
}

function generateChecklistData(category) {

    let plumbingChecked = "na";
    let cupboardsChecked = "na";
    let plumbingComments = "na";
    let cupboardsComments = "na";
    let stoveChecked = "na";
    let stoveComments = "na";

    if(category.localeCompare("kitchen") === 0){
        plumbingChecked = document.getElementById(`${category}Plumbing`).checked;
        cupboardsChecked = document.getElementById(`${category}Cupboards`).checked;
        stoveChecked = document.getElementById(`${category}Stove`).checked;

        plumbingComments = document.getElementById(`${category}PlumbingNote`).value;
        cupboardsComments = document.getElementById(`${category}CupboardsNote`).value;
        stoveComments = document.getElementById(`${category}StoveNote`).value;

    }

    const wallsChecked = document.getElementById(`${category}Walls`).checked;
    const doorsChecked = document.getElementById(`${category}Doors`).checked;
    const windowsChecked = document.getElementById(`${category}Windows`).checked;

    const lightingChecked = document.getElementById(`${category}Lighting`).checked;
    const floorsChecked = document.getElementById(`${category}Floors`).checked;


    const wallsComments = document.getElementById(`${category}WallsNote`).value;
    const doorsComments = document.getElementById(`${category}DoorsNote`).value;
    const windowsComments = document.getElementById(`${category}WindowsNote`).value;
    const lightingComments = document.getElementById(`${category}LightingNote`).value;
    const floorsComments = document.getElementById(`${category}FloorsNote`).value;

    return {
        plumbing: plumbingChecked,
        plumbingComments: plumbingComments,
        cupboards: cupboardsChecked,
        cupboardComments: cupboardsComments,
        walls: wallsChecked,
        wallsComments: wallsComments,
        doors: doorsChecked,
        doorsComments: doorsComments,
        windows: windowsChecked,
        windowsComments: windowsComments,
        lighting: lightingChecked,
        lightingComments: lightingComments,
        floors: floorsChecked,
        floorsComments: floorsComments,
        stove: stoveChecked,
        stoveComments: stoveComments,
    };
}

let showToast = (message) =>{
    const liveToast = document.getElementById('liveToast')
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(liveToast)
    if(message.toLowerCase().includes("success")){
        $('#toast-message').html('<div class="alert alert-success" role="alert">'+message+'</div>');
    }else if(message.toLowerCase().includes("fail") || message.toLowerCase().includes("error")){
        $('#toast-message').html('<div class="alert alert-danger" role="alert">'+message+'</div>');
    }else{
        $('#toast-message').html('<div class="alert alert-dark" role="alert">'+message+'</div>');
    }
    toastBootstrap.show();
}