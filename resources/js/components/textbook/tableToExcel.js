export default function tableToExcel(headers,jsonData,fileName){


    let titleLine = headers.map(e => {
        return `<td>${e.name}</td>`
    }).join('');
    let str = `<tr>${titleLine}</tr>`;
    for(let i = 0 ; i < jsonData.length ; i++ ){
      str+='<tr>';
      headers.map(item => {
        str+=`<td STYLE='MSO-NUMBER-FORMAT:\\@'">${ item.formatter(jsonData[i]) +'\t' }</td>`;
      })

      str+='</tr>';
    }
    let worksheet = fileName
    let uri = 'data:application/vnd.ms-excel;base64,';

    let template = `<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
    <head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
      <x:Name>${worksheet}</x:Name>
      <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>
      </x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
      </head><body><table>${str}</table></body></html>`;
    var link = document.createElement("a");
    link.href = uri + base64(template)
    link.download = fileName +new Date().getTime()+ ".xls";
    link.style = "visibility:hidden";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
  function base64 (s) { return window.btoa(unescape(encodeURIComponent(s)))}
