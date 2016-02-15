angular
    .module('app')
    .controller('AuthController',  ['$auth', '$state','$http','$rootScope','$scope' , function($auth, $state,$http,$rootScope, $scope) {
         
            $scope.email='';
            $scope.password='';
            $scope.newUser={};
            $scope.loginError=false;
            $scope.loginErrorText='';
         
            $scope.login = function() {
     
                var credentials = {
                    email: $scope.email,
                    password: $scope.password
                }
     
                $auth.login(credentials).then(function() {
     
                    return $http.get('api/authenticate/user');
     
                }, function(error) {
                    $scope.loginError = true;
                    $scope.loginErrorText = error.data.error;
     
                }).then(function(response) {
                    $rootScope.currentUser = response.data.user;
                    $scope.loginError = false;
                    $scope.loginErrorText = '';
     
                    $state.go('home');
                });
            };
     
            $scope.register = function () {
     
                $http.post('/api/register',$scope.newUser)
                    .success(function(data){
                        $scope.email=$scope.newUser.email;
                        $scope.password=$scope.newUser.password;
                        $scope.login();
                })
     
            };

            //logout function
            $scope.logout = function() {
                $auth.logout().then(function() {
                     $rootScope.currentUser = null;
                });
            };
         
        }]);