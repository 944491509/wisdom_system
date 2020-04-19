(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[1],{

/***/ "./node_modules/raw-loader/index.js!./node_modules/babel-loader/lib/index.js?!./resources/js/includes/backend/teacher-attendance_manager/js/Blob.js":
/*!*************************************************************************************************************************************************!*\
  !*** ./node_modules/raw-loader!./node_modules/babel-loader/lib??ref--4-0!./resources/js/includes/backend/teacher-attendance_manager/js/Blob.js ***!
  \*************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = "/* eslint-disable */\n\n/* Blob.js\r\n * A Blob implementation.\r\n * 2014-05-27\r\n *\r\n * By Eli Grey, http://eligrey.com\r\n * By Devin Samarin, https://github.com/eboyjr\r\n * License: X11/MIT\r\n *   See LICENSE.md\r\n */\n\n/*global self, unescape */\n\n/*jslint bitwise: true, regexp: true, confusion: true, es5: true, vars: true, white: true,\r\n plusplus: true */\n\n/*! @source http://purl.eligrey.com/github/Blob.js/blob/master/Blob.js */\n(function (view) {\n  \"use strict\";\n\n  view.URL = view.URL || view.webkitURL;\n\n  if (view.Blob && view.URL) {\n    try {\n      new Blob();\n      return;\n    } catch (e) {}\n  } // Internally we use a BlobBuilder implementation to base Blob off of\n  // in order to support older browsers that only have BlobBuilder\n\n\n  var BlobBuilder = view.BlobBuilder || view.WebKitBlobBuilder || view.MozBlobBuilder || function (view) {\n    var get_class = function get_class(object) {\n      return Object.prototype.toString.call(object).match(/^\\[object\\s(.*)\\]$/)[1];\n    },\n        FakeBlobBuilder = function BlobBuilder() {\n      this.data = [];\n    },\n        FakeBlob = function Blob(data, type, encoding) {\n      this.data = data;\n      this.size = data.length;\n      this.type = type;\n      this.encoding = encoding;\n    },\n        FBB_proto = FakeBlobBuilder.prototype,\n        FB_proto = FakeBlob.prototype,\n        FileReaderSync = view.FileReaderSync,\n        FileException = function FileException(type) {\n      this.code = this[this.name = type];\n    },\n        file_ex_codes = (\"NOT_FOUND_ERR SECURITY_ERR ABORT_ERR NOT_READABLE_ERR ENCODING_ERR \" + \"NO_MODIFICATION_ALLOWED_ERR INVALID_STATE_ERR SYNTAX_ERR\").split(\" \"),\n        file_ex_code = file_ex_codes.length,\n        real_URL = view.URL || view.webkitURL || view,\n        real_create_object_URL = real_URL.createObjectURL,\n        real_revoke_object_URL = real_URL.revokeObjectURL,\n        URL = real_URL,\n        btoa = view.btoa,\n        atob = view.atob,\n        ArrayBuffer = view.ArrayBuffer,\n        Uint8Array = view.Uint8Array;\n\n    FakeBlob.fake = FB_proto.fake = true;\n\n    while (file_ex_code--) {\n      FileException.prototype[file_ex_codes[file_ex_code]] = file_ex_code + 1;\n    }\n\n    if (!real_URL.createObjectURL) {\n      URL = view.URL = {};\n    }\n\n    URL.createObjectURL = function (blob) {\n      var type = blob.type,\n          data_URI_header;\n\n      if (type === null) {\n        type = \"application/octet-stream\";\n      }\n\n      if (blob instanceof FakeBlob) {\n        data_URI_header = \"data:\" + type;\n\n        if (blob.encoding === \"base64\") {\n          return data_URI_header + \";base64,\" + blob.data;\n        } else if (blob.encoding === \"URI\") {\n          return data_URI_header + \",\" + decodeURIComponent(blob.data);\n        }\n\n        if (btoa) {\n          return data_URI_header + \";base64,\" + btoa(blob.data);\n        } else {\n          return data_URI_header + \",\" + encodeURIComponent(blob.data);\n        }\n      } else if (real_create_object_URL) {\n        return real_create_object_URL.call(real_URL, blob);\n      }\n    };\n\n    URL.revokeObjectURL = function (object_URL) {\n      if (object_URL.substring(0, 5) !== \"data:\" && real_revoke_object_URL) {\n        real_revoke_object_URL.call(real_URL, object_URL);\n      }\n    };\n\n    FBB_proto.append = function (data\n    /*, endings*/\n    ) {\n      var bb = this.data; // decode data to a binary string\n\n      if (Uint8Array && (data instanceof ArrayBuffer || data instanceof Uint8Array)) {\n        var str = \"\",\n            buf = new Uint8Array(data),\n            i = 0,\n            buf_len = buf.length;\n\n        for (; i < buf_len; i++) {\n          str += String.fromCharCode(buf[i]);\n        }\n\n        bb.push(str);\n      } else if (get_class(data) === \"Blob\" || get_class(data) === \"File\") {\n        if (FileReaderSync) {\n          var fr = new FileReaderSync();\n          bb.push(fr.readAsBinaryString(data));\n        } else {\n          // async FileReader won't work as BlobBuilder is sync\n          throw new FileException(\"NOT_READABLE_ERR\");\n        }\n      } else if (data instanceof FakeBlob) {\n        if (data.encoding === \"base64\" && atob) {\n          bb.push(atob(data.data));\n        } else if (data.encoding === \"URI\") {\n          bb.push(decodeURIComponent(data.data));\n        } else if (data.encoding === \"raw\") {\n          bb.push(data.data);\n        }\n      } else {\n        if (typeof data !== \"string\") {\n          data += \"\"; // convert unsupported types to strings\n        } // decode UTF-16 to binary string\n\n\n        bb.push(unescape(encodeURIComponent(data)));\n      }\n    };\n\n    FBB_proto.getBlob = function (type) {\n      if (!arguments.length) {\n        type = null;\n      }\n\n      return new FakeBlob(this.data.join(\"\"), type, \"raw\");\n    };\n\n    FBB_proto.toString = function () {\n      return \"[object BlobBuilder]\";\n    };\n\n    FB_proto.slice = function (start, end, type) {\n      var args = arguments.length;\n\n      if (args < 3) {\n        type = null;\n      }\n\n      return new FakeBlob(this.data.slice(start, args > 1 ? end : this.data.length), type, this.encoding);\n    };\n\n    FB_proto.toString = function () {\n      return \"[object Blob]\";\n    };\n\n    FB_proto.close = function () {\n      this.size = this.data.length = 0;\n    };\n\n    return FakeBlobBuilder;\n  }(view);\n\n  view.Blob = function Blob(blobParts, options) {\n    var type = options ? options.type || \"\" : \"\";\n    var builder = new BlobBuilder();\n\n    if (blobParts) {\n      for (var i = 0, len = blobParts.length; i < len; i++) {\n        builder.append(blobParts[i]);\n      }\n    }\n\n    return builder.getBlob(type);\n  };\n})(typeof self !== \"undefined\" && self || typeof window !== \"undefined\" && window || this.content || this);"

/***/ }),

/***/ "./node_modules/script-loader/index.js!./resources/js/includes/backend/teacher-attendance_manager/js/Blob.js":
/*!**********************************************************************************************************!*\
  !*** ./node_modules/script-loader!./resources/js/includes/backend/teacher-attendance_manager/js/Blob.js ***!
  \**********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! !./node_modules/script-loader/addScript.js */ "./node_modules/script-loader/addScript.js")(__webpack_require__(/*! !./node_modules/raw-loader!./node_modules/babel-loader/lib??ref--4-0!./resources/js/includes/backend/teacher-attendance_manager/js/Blob.js */ "./node_modules/raw-loader/index.js!./node_modules/babel-loader/lib/index.js?!./resources/js/includes/backend/teacher-attendance_manager/js/Blob.js"))

/***/ }),

/***/ "./resources/js/includes/backend/teacher-attendance_manager/js/Export2Excel.js":
/*!*************************************************************************************!*\
  !*** ./resources/js/includes/backend/teacher-attendance_manager/js/Export2Excel.js ***!
  \*************************************************************************************/
/*! exports provided: export_table_to_excel, export_json_to_excel */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "export_table_to_excel", function() { return export_table_to_excel; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "export_json_to_excel", function() { return export_json_to_excel; });
/* eslint-disable */
__webpack_require__(/*! script-loader!file-saver */ "./node_modules/script-loader/index.js!./node_modules/file-saver/dist/FileSaver.min.js");

__webpack_require__(/*! script-loader!./Blob */ "./node_modules/script-loader/index.js!./resources/js/includes/backend/teacher-attendance_manager/js/Blob.js");

__webpack_require__(/*! script-loader!xlsx/dist/xlsx.core.min */ "./node_modules/script-loader/index.js!./node_modules/xlsx/dist/xlsx.core.min.js");

function generateArray(table) {
  var out = [];
  var rows = table.querySelectorAll('tr');
  var ranges = [];

  for (var R = 0; R < rows.length; ++R) {
    var outRow = [];
    var row = rows[R];
    var columns = row.querySelectorAll('td');

    for (var C = 0; C < columns.length; ++C) {
      var cell = columns[C];
      var colspan = cell.getAttribute('colspan');
      var rowspan = cell.getAttribute('rowspan');
      var cellValue = cell.innerText;
      if (cellValue !== "" && cellValue == +cellValue) cellValue = +cellValue; //Skip ranges

      ranges.forEach(function (range) {
        if (R >= range.s.r && R <= range.e.r && outRow.length >= range.s.c && outRow.length <= range.e.c) {
          for (var i = 0; i <= range.e.c - range.s.c; ++i) {
            outRow.push(null);
          }
        }
      }); //Handle Row Span

      if (rowspan || colspan) {
        rowspan = rowspan || 1;
        colspan = colspan || 1;
        ranges.push({
          s: {
            r: R,
            c: outRow.length
          },
          e: {
            r: R + rowspan - 1,
            c: outRow.length + colspan - 1
          }
        });
      }

      ; //Handle Value

      outRow.push(cellValue !== "" ? cellValue : null); //Handle Colspan

      if (colspan) for (var k = 0; k < colspan - 1; ++k) {
        outRow.push(null);
      }
    }

    out.push(outRow);
  }

  return [out, ranges];
}

;

function datenum(v, date1904) {
  if (date1904) v += 1462;
  var epoch = Date.parse(v);
  return (epoch - new Date(Date.UTC(1899, 11, 30))) / (24 * 60 * 60 * 1000);
}

function sheet_from_array_of_arrays(data, opts) {
  var ws = {};
  var range = {
    s: {
      c: 10000000,
      r: 10000000
    },
    e: {
      c: 0,
      r: 0
    }
  };

  for (var R = 0; R != data.length; ++R) {
    for (var C = 0; C != data[R].length; ++C) {
      if (range.s.r > R) range.s.r = R;
      if (range.s.c > C) range.s.c = C;
      if (range.e.r < R) range.e.r = R;
      if (range.e.c < C) range.e.c = C;
      var cell = {
        v: data[R][C]
      };
      if (cell.v == null) continue;
      var cell_ref = XLSX.utils.encode_cell({
        c: C,
        r: R
      });
      if (typeof cell.v === 'number') cell.t = 'n';else if (typeof cell.v === 'boolean') cell.t = 'b';else if (cell.v instanceof Date) {
        cell.t = 'n';
        cell.z = XLSX.SSF._table[14];
        cell.v = datenum(cell.v);
      } else cell.t = 's';
      ws[cell_ref] = cell;
    }
  }

  if (range.s.c < 10000000) ws['!ref'] = XLSX.utils.encode_range(range);
  return ws;
}

function Workbook() {
  if (!(this instanceof Workbook)) return new Workbook();
  this.SheetNames = [];
  this.Sheets = {};
}

function s2ab(s) {
  var buf = new ArrayBuffer(s.length);
  var view = new Uint8Array(buf);

  for (var i = 0; i != s.length; ++i) {
    view[i] = s.charCodeAt(i) & 0xFF;
  }

  return buf;
}

function export_table_to_excel(id) {
  var theTable = document.getElementById(id);
  console.log('a');
  var oo = generateArray(theTable);
  var ranges = oo[1];
  /* original data */

  var data = oo[0];
  var ws_name = "SheetJS";
  console.log(data);
  var wb = new Workbook(),
      ws = sheet_from_array_of_arrays(data);
  /* add ranges to worksheet */
  // ws['!cols'] = ['apple', 'banan'];

  ws['!merges'] = ranges;
  /* add worksheet to workbook */

  wb.SheetNames.push(ws_name);
  wb.Sheets[ws_name] = ws;
  var wbout = XLSX.write(wb, {
    bookType: 'xlsx',
    bookSST: false,
    type: 'binary'
  });
  saveAs(new Blob([s2ab(wbout)], {
    type: "application/octet-stream"
  }), "test.xlsx");
}

function formatJson(jsonData) {
  console.log(jsonData);
}

function export_json_to_excel(th, jsonData, defaultTitle) {
  /* original data */
  var data = jsonData;
  data.unshift(th);
  var ws_name = "SheetJS";
  var wb = new Workbook(),
      ws = sheet_from_array_of_arrays(data);
  /* add worksheet to workbook */

  wb.SheetNames.push(ws_name);
  wb.Sheets[ws_name] = ws;
  var wbout = XLSX.write(wb, {
    bookType: 'xlsx',
    bookSST: false,
    type: 'binary'
  });
  var title = defaultTitle || '列表';
  saveAs(new Blob([s2ab(wbout)], {
    type: "application/octet-stream"
  }), title + ".xlsx");
}

/***/ })

}]);