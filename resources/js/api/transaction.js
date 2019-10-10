import request from '@/utils/request';
import Resource from '@/api/resource';

class UnitResource extends Resource {
  constructor() {
    super('transactions');
  }

  changeStatus(id) {
    return request({
      url: '/' + this.uri + '/changeStatus/' + id,
      method: 'get',
    });
  }

  updatePermission(id, permissions) {
    return request({
      url: '/' + this.uri + '/' + id + '/permissions',
      method: 'put',
      data: permissions,
    });
  }
}

export { UnitResource as default };
