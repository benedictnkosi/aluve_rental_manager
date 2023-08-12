$(document).ready(function () {
// Call the function to generate the HTML
    getInspection();
    getInspectionDetails();


});

let getInspection = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const guid = urlParams.get('guid');
    let url = "/api/inspection/" + guid
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            if(data.result_code === 1){
                $("#header").html("No Previous Inspections Found");
                $("#inspection_images").html("");
            }else{
                $("#header").html("Inspection - " + data.date);
                let newData = replaceAll(data.json, "true", '"No Issues"');
                newData = replaceAll(newData, "false", '"Issues Identified"');
                convertJSONtoHTML(JSON.parse(newData));
                populateImages(data.images)
            }

        },
        error: function (xhr) {
            console.log("error occured")
        }
    });
}

let getInspectionDetails = () => {
    const queryString = window.location.search;
    console.log(queryString);
    const urlParams = new URLSearchParams(queryString);
    const guid = urlParams.get('guid');
    let url = "/api/lease/" + guid
    $.ajax({
        type: "GET",
        url: url,
        contentType: "application/json; charset=UTF-8",
        success: function (data) {
            $("#property-name").html(data.property_name);
            $("#unit-name").html(data.unit_name);
        },
        error: function (xhr) {

        }
    });
}

function replaceAll(str, find, replace) {
    return str.replace(new RegExp(find, 'g'), replace);
}


// Function to convert JSON to HTML
function populateImages(jsonData) {
    let html = '';
    jsonData.forEach((image) => {
        html += '<div class="carousel-item active">\n' +
            '                    <img src="/api/inspection_image/'+image.name+'" class="d-block w-100" alt='+image.name+'">\n' +
            '                </div>';
    });
    // Add HTML to the page
    document.getElementById('inspection_images').innerHTML = html;
}


// Function to convert JSON to HTML
function convertJSONtoHTML(jsonData) {
    let html = '';


    // Convert bedroom checklist
    html += '<h5 class="mt-5 inspection-page-header  text-center">Bedroom Checklist</h5>';
    jsonData.bedroomChecklist.forEach((item) => {
        html += '<div class="card">';
        html += `<div class="card-header">Bedroom ${item.bedroomNumber}</div>`;
        html += '<div class="card-body">';
        html += '<table class="table">\n' +
            '  <thead>\n' +
            '    <tr>\n' +
            '      <th scope="col">ITEM</th>\n' +
            '      <th scope="col">RESULTS</th>\n' +
            '      <th scope="col">COMMENTS</th>\n' +
            '    </tr>\n' +
            '  </thead>' +
            '<tbody>';
        html += `<tr><td>Wardrobe </td><td>${item.wardrobe}</td><td>${item.wardrobeComments}</td></tr>`;
        html += `<tr><td>Walls </td><td>${item.walls}</td><td>${item.wallsComments}</td></tr>`;
        html += `<tr><td>Doors </td><td>${item.doors}</td><td>${item.doorsComments}</td></tr>`;
        html += `<tr><td>Windows </td><td>${item.windows}</td><td>${item.windowsComments}</td></tr>`;
        html += `<tr><td>Lighting </td><td>${item.lighting}</td><td>${item.lightingComments}</td></tr>`;
        html += `<tr><td>Floors </td><td>${item.floors}</td><td>${item.floorsComments}</td></tr>`;
        html += '</tbody>\n' +
            '</table>';
        html += '</div>';
        html += '</div>';
    });

    // Convert kitchen checklist
    html += '<h5 class="mt-5 inspection-page-header  text-center">Kitchen Checklist</h5>';
    html += '<div class="card">';
    html += '<div class="card-body">';
    html += '<table class="table">\n' +
        '  <thead>\n' +
        '    <tr>\n' +
        '      <th scope="col">ITEM</th>\n' +
        '      <th scope="col">RESULTS</th>\n' +
        '      <th scope="col">COMMENTS</th>\n' +
        '    </tr>\n' +
        '  </thead>' +
        '<tbody>';
    html += `<tr><td>Plumbing </td><td>${jsonData.kitchenChecklist.plumbing}</td><td>${jsonData.kitchenChecklist.plumbingComments}</td></tr>`;
    html += `<tr><td>Cupboards </td><td>${jsonData.kitchenChecklist.cupboards}</td><td>${jsonData.kitchenChecklist.cupboardComments}</td></tr>`;
    html += `<tr><td>Stove </td><td>${jsonData.kitchenChecklist.stove}</td><td>${jsonData.kitchenChecklist.stoveComments}</td></tr>`;
    html += `<tr><td>Walls </td><td>${jsonData.kitchenChecklist.walls}</td><td>${jsonData.kitchenChecklist.wallsComments}</td></tr>`;
    html += `<tr><td>Doors </td><td>${jsonData.kitchenChecklist.doors}</td><td>${jsonData.kitchenChecklist.doorsComments}</td></tr>`;
    html += `<tr><td>Windows </td><td>${jsonData.kitchenChecklist.windows}</td><td>${jsonData.kitchenChecklist.windowsComments}</td></tr>`;
    html += `<tr><td>Lighting </td><td>${jsonData.kitchenChecklist.lighting}</td><td>${jsonData.kitchenChecklist.lightingComments}</td></tr>`;
    html += `<tr><td>Floors </td><td>${jsonData.kitchenChecklist.floors}</td><td>${jsonData.kitchenChecklist.floorsComments}</td></tr>`;

    html += '</tbody>\n' +
        '</table>';
    html += '</div>';
    html += '</div>';

    // Convert living room checklist
    html += '<h5 class="mt-5 inspection-page-header  text-center">Living Room Checklist</h5>';
    html += '<div class="card">';
    html += '<div class="card-body">';
    html += '<table class="table">\n' +
        '  <thead>\n' +
        '    <tr>\n' +
        '      <th scope="col">ITEM</th>\n' +
        '      <th scope="col">RESULTS</th>\n' +
        '      <th scope="col">COMMENTS</th>\n' +
        '    </tr>\n' +
        '  </thead>' +
        '<tbody>';
    html += `<tr><td>Walls </td><td>${jsonData.livingRoomChecklist.walls}</td><td>${jsonData.livingRoomChecklist.wallsComments}</td></tr>`;
    html += `<tr><td>Doors </td><td>${jsonData.livingRoomChecklist.doors}</td><td>${jsonData.livingRoomChecklist.doorsComments}</td></tr>`;
    html += `<tr><td>Windows </td><td>${jsonData.livingRoomChecklist.windows}</td><td>${jsonData.livingRoomChecklist.windowsComments}</td></tr>`;
    html += `<tr><td>Lighting </td><td>${jsonData.livingRoomChecklist.lighting}</td><td>${jsonData.livingRoomChecklist.lightingComments}</td></tr>`;
    html += `<tr><td>Floors </td><td>${jsonData.livingRoomChecklist.floors}</td><td>${jsonData.livingRoomChecklist.floorsComments}</td></tr>`;

    html += '</tbody>\n' +
        '</table>';
    html += '</div>';
    html += '</div>';

    // Convert bathroom checklist
    html += '<h5 class="mt-5 inspection-page-header  text-center">Bathroom Checklist</h5>';
    jsonData.bathroomChecklist.forEach((item) => {
        html += '<div class="card">';
        html += `<div class="card-header">Bathroom ${item.bathroomNumber}</div>`;
        html += '<div class="card-body">';
        html += '<table class="table">\n' +
            '  <thead>\n' +
            '    <tr>\n' +
            '      <th scope="col">ITEM</th>\n' +
            '      <th scope="col">RESULTS</th>\n' +
            '      <th scope="col">COMMENTS</th>\n' +
            '    </tr>\n' +
            '  </thead>' +
            '<tbody>';
        html += `<tr><td>Vanity </td><td>${item.vanity}</td><td>${item.vanityComments}</td></tr>`;
        html += `<tr><td>Plumbing </td><td>${item.plumbing}</td><td>${item.plumbingComments}</td></tr>`;
        html += `<tr><td>Toilet </td><td>${item.toilet}</td><td>${item.toiletComments}</td></tr>`;
        html += `<tr><td>Shower </td><td>${item.showerTub}</td><td>${item.showerComments}</td></tr>`;
        html += `<tr><td>Medicine Cabinet </td><td>${item.medicineCabinet}</td><td>${item.medicineCabinetComments}</td></tr>`;
        html += `<tr><td>Walls </td><td>${item.walls}</td><td>${item.wallsComments}</td></tr>`;
        html += `<tr><td>Doors </td><td>${item.doors}</td><td>${item.doorsComments}</td></tr>`;
        html += `<tr><td>Windows </td><td>${item.windows}</td><td>${item.windowsComments}</td></tr>`;
        html += `<tr><td>Lighting </td><td>${item.lighting}</td><td>${item.lightingComments}</td></tr>`;
        html += `<tr><td>Floors </td><td>${item.floors}</td><td>${item.floorsComments}</td></tr>`;
        html += '</tbody>\n' +
            '</table>';
        html += '</div>';
        html += '</div>';
    });

    // Add HTML to the page
    document.getElementById('inspection_div').innerHTML = html;
}



