import Cookies from 'js-cookie';

const TokenKey = 'Admin-Token';
const Role = 'Role';

export function getToken() {
  return Cookies.get(TokenKey);
}

export function setToken(token) {
  return Cookies.set(TokenKey, token);
}

export function removeToken() {
  return Cookies.remove(TokenKey);
}

export function removeRole() {
  return Cookies.remove(Role);
}

export function setRole(role) {
  return Cookies.set(Role, role);
}
