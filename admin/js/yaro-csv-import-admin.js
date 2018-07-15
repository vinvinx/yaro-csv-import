class Application {
  ajaxImport() {
    const obj = this;
    $('#import-btn').on('click', function () {
      event.preventDefault(true);
      const data = {
        action: 'ajaxImportItem',
        offset: 0,
      };
      obj.importItem(data);
    });
  }
  importItem(data) {
    const obj = this;
    $.post(ajaxurl, data, function (response) {
      if (response.error) {
      } else {
        const data = {
          action: 'ajaxImportItem',
          offset: response.offset,
        };
        $('#import-log').append(response.message);
        obj.importItem(data);
      }
    });
  }
}

const app = new Application();
app.ajaxImport();
