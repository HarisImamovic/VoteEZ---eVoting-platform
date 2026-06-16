var UserService = {
    init: function () {
      var token = localStorage.getItem("user_token");
      if (token && token !== undefined) {
        window.location.replace("index.html");
      }
      $("#loginForm").validate({
        rules: {
          email: {
            required: true,
            email: true
          },
          password: {
            required: true
          },
          phone: {
            required: true,
            digits: true
        }
        },
        messages: {
          email:{
            required: "Please enter your email",
            email: "Please enter a valid email address"
          },
          password:{
            required: "Please enter your password"
          },
          phone:{
            required: "Please enter your phone number",
            digits: "Phone number must contain only digits"
          }
        },
        submitHandler: function (form) {
          var entity = Object.fromEntries(new FormData(form).entries());
          UserService.login(entity);
        },
      });
      $("#registerForm").validate({
        rules: {
          name: {
            required: true
          },
          email: {
            required: true,
            email: true
          },
          password: {
            required: true,
            minlength: 8
          },
          phone: {
            required: true,
            digits: true
          }
        },
        messages: {
          name: {
            required: "Please enter your name"
          },
          email: {
            required: "Please enter your email",
            email: "Please enter a valid email"
          },
          password: {
            required: "Please enter a password",
            minlength: "Password must be at least 8 characters"
          },
          phone: {
            required: "Please enter your phone number",
            digits: "Phone number must contain only digits"
          }
        },
        submitHandler: function (form) {
          var entity = Object.fromEntries(new FormData(form).entries());
          UserService.register(entity);
        }
      });
    },
    validateForms: function(){  
        $("#edit-user-form").validate({
             rules: {
                name: 'required',
                email: {
                  required: true,
                  email: true
                },
                phone: {
                    required: true,
                    digits: true,
                },
                role: {
                    required: true,
                }
            },
            messages: {
                name: 'Please enter the user\'s name',
                email:{
                    required: 'Please enter the user\'s email',
                    email: 'Please enter a valid email'
                },
                phone: {
                    required: 'Please enter a valid phone number',
                    digits: 'Please enter a number'
                },
                role: {
                    required: 'Please enter the user\'s role',
                }
            },
            submitHandler: function (form) {
              let user = Object.fromEntries(new FormData(form).entries());
              UserService.editUser(user);
            },
        });
        $("#add-user-form").validate({
          rules: {
            name: 'required',
            email: {
              required: true,
              email: true,
            },
            phone: {
              required: true,
              digits: true,
            },
            password:{
              required: true
            },
            has_voted:{
              required: true,
              digits: true,
              range: [0,0]
            },
            role:{
              required: true
            }
          },
          messages: {
                name: 'Please enter the user\'s name',
                email:{
                    required: 'Please enter the user\'s email',
                    email: 'Please enter a valid email'
                },
                phone: {
                    required: 'Please enter a valid phone number',
                    digits: 'Please enter a number'
                },
                password: {
                  required: 'Please enter a password'
                },
                has_voted:{
                  required: 'Please enter the user\'s vote status as 0',
                  digits: 'Please enter the number 0',
                  range: 'The number has to be zero'
                },
                role: {
                    required: 'Please enter the user\'s role',
                }
          },
          submitHandler: function(form){
            let user = Object.fromEntries(new FormData(form).entries());
            UserService.addUser(user);
            form.reset();
          }
        });
        UserService.generateUsersDatatable();
    },
    login: function (entity) {
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "auth/login",
        type: "POST",
        data: JSON.stringify(entity),
        contentType: "application/json",
        dataType: "json",
        success: function (result) {
          localStorage.setItem("user_token", result.data.token);
          window.location.replace("index.html");
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
          toastr.error(XMLHttpRequest?.responseText ?  XMLHttpRequest.responseText : 'Error');
        },
      });
    },
    register: function(entity){
      $.ajax({
        url: Constants.PROJECT_BASE_URL + "auth/register",
        type: "POST",
        data: JSON.stringify(entity),
        contentType: "application/json",
        dataType: "json",
        success: function(result){
          window.location.replace("login.html");
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
          toastr.error(XMLHttpRequest?.responseText ? XMLHttpRequest.responseText : 'Error');
        },
      });
    },
    logout: function () {
      localStorage.clear();
      window.location.replace("login.html");
    },
  
    generateMenuItems: function(){
      const token = localStorage.getItem("user_token");
      if (token) {
        const user = Utils.parseJwt(token).user;
        if (user && user.role) {
          let nav = "";
          let main = "";
        
          switch(user.role) {
            case Constants.USER_ROLE:
              nav = '<li class="nav-item">'+
                    '<a class="nav-link fs-5" aria-current="page" href="#home">Home</a>'+
                '</li>'+
                '<li class="nav-item">'+
                    '<a class="nav-link fs-5" href="#vote">Vote</a>'+
                '</li>'+
                '<li class="nav-item">'+
                    '<a class="nav-link fs-5" href="#candidates">Candidates</a>'+
                '</li>'+
                '<li class="nav-item">'+
                    '<a class="nav-link fs-5" onclick="UserService.logout()" id="logoutButton">Logout</a>'+
                '</li>';
              $("#menuTabs").html(nav);

              main =
                '<section id="home" data-load="home.html"></section>'+
                '<section id="candidates" data-load="candidates.html"></section>'+
                '<section id="vote" data-load="vote.html"></section>'+
                '<section id="contact" data-load="contact.html"></section>';
              $("#spapp").html(main);
            break;

          case Constants.ADMIN_ROLE:
            nav ='<li class="nav-item">'+
                    '<a class="nav-link fs-5" aria-current="page" href="#home">Home</a>'+
                '</li>'+
                '<li class="nav-item">'+
                    '<a class="nav-link fs-5" href="#vote">Vote</a>'+
                '</li>'+
                '<li class="nav-item">'+
                    '<a class="nav-link fs-5" href="#candidates">Candidates</a>'+
                '</li>'+
                '<li class="nav-item">'+
                    '<a class="nav-link fs-5" onclick="UserService.logout()" id="logoutButton">Logout</a>'+
                '</li>';
            $("#menuTabs").html(nav);

            main =
                '<section id="home" data-load="home.html"></section>'+
                '<section id="candidates" data-load="candidates.html"></section>'+
                '<section id="vote" data-load="vote.html"></section>'+
                '<section id="contact" data-load="contact.html"></section>';
            $("#spapp").html(main);
            break;

          default:
            $("#menuTabs").html(nav);
            $("#spapp").html(main);
        }
      } else {
        window.location.replace("login.html");
      }
    } else {
      window.location.replace("login.html");
    }
    },

    generateAdminViewVotesButton: function(){
      const token = localStorage.getItem("user_token");
      if (token) {
        const user = Utils.parseJwt(token).user;
        if(user && user.role){
          let html = "";
          if(user.role === Constants.ADMIN_ROLE){
            html=`<a class="text-decoration-none text-danger" href="#vote">
                        Vote <i class="bi bi-arrow-right"></i>
                  </a>
                  <a class="text-decoration-none text-danger ms-5" 
                  id="admin-view-votes" data-bs-toggle="modal" data-bs-target="#exampleModal4" href="#";>
                  View votes <i class="bi bi-arrow-right"></i>
                  </a>`;
            $("#row-for-admin").html(html);
            $("#row-for-admin-inquiries").append(`<a class="text-decoration-none text-danger" href="#contact">
                    Contact
                    <i class="bi bi-arrow-right "></i>
                </a>`);
            $("#row-for-admin-inquiries").append(`<a class="text-decoration-none text-danger ms-5" 
                  id="admin-view-inquiries" data-bs-toggle="modal" data-bs-target="#exampleModal5" href="#" onclick="InquiryService.populateViewInquiriesModal()";>
                  View inquiries <i class="bi bi-arrow-right"></i>
                  </a>`);
          }
          else{
            html=`<a class="text-decoration-none text-danger" href="#vote">
            Vote <i class="bi bi-arrow-right"></i>
            </a>`;
            $("#row-for-admin").html(html);
            $("#row-for-admin-inquiries").append(`<a class="text-decoration-none text-danger" href="#contact">
                    Contact
                    <i class="bi bi-arrow-right "></i>
            </a>`);
          }
        }
      }else{
        window.location.replace("login.html");
      }
    },
    generateDatatableAdminButton: function(){
      const token = localStorage.getItem("user_token");
      if(token){
        const user = Utils.parseJwt(token).user;
        if(user && user.role){
          let adminButton = "";
          if(user.role === Constants.ADMIN_ROLE){
            adminButton =`<button type="button" class="btn btn-danger btn-sm" id="admin-add-button" data-bs-toggle="modal" data-bs-target="#exampleModal4" style="margin-left: 1.5vh;">Add candidate</button>`;
            $("#buttonContainer").append(adminButton);
          }
        }
      }else{
        window.location.replace("login.html");
      }
    },

    generateUsersDatatable: function(){
      const token = localStorage.getItem("user_token");
      if(token){
        const user = Utils.parseJwt(token).user;
        if(user && user.role){
          if(user.role === Constants.ADMIN_ROLE){
            RestClient.get('users', function(data){
              $("#admin-users-section").empty();
              $("#admin-users-section").append(`<section class="py-5 border-top" id="registered-voters"></section>`);
              $("#registered-voters").empty();
              $("#registered-voters").append(`
                  <div class="container px-5 my-5">
                  <div class="text-center mb-5">
                    <h2 class="fw-bolder">Currently Registered Voters</h2>
                    <p class="lead mb-0 text-muted">Below is a list of all voters currently registered in the system</p>
                  </div>
                  <div id="admin-add-user-button-div">
                  
                  </div>
                  <div class="table-responsive">
                  <table id="votersTable" class="table table-dark table-bordered text-center">
                  </table>
                  </div>
                  </div>
              
              `);
              $("#admin-add-user-button-div").empty();
              $("#admin-add-user-button-div").append(`<button type="button" class="btn btn-danger btn-sm" id="admin-add-user-button" data-bs-toggle="modal" data-bs-target="#exampleUsersModal4" style="margin-left: 1.5vh;">Add user</button>`);
              Utils.datatable("votersTable", [
                  {data: 'name', title: 'Name'},
                  {data: 'email', title: 'Email'},
                  {data: 'phone', title: 'Phone'},
                  {title: 'Actions',
                    render: function(data, type, row, meta){
                                const rowStr = encodeURIComponent(JSON.stringify(row));
                                return `<div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-success btn-sm" id="admin-view-user-button" data-bs-toggle="modal" data-bs-target="#exampleUsersModal" onclick="UserService.populateUserViewMoreModal('${row.id}')">View</button>
                                        <button type="button" class="btn btn-info btn-sm" id="admin-edit-user-button" data-bs-toggle="modal" data-bs-target="#exampleUsersModal2" onclick="UserService.openUserEditModal('${row.id}')">Edit</button>
                                        <button type="button" class="btn btn-danger btn-sm" id="admin-delete-user-button" data-bs-toggle="modal" data-bs-target="#exampleUsersModal3" onclick="UserService.openUserConfirmationDialog(decodeURIComponent('${rowStr}'))">Delete</button>
                                    </div>`;
                    }
                  }
            ], data, 10 );

            }, function(xhr, status, error){
              console.error(error);
            });
          }
        }
      }else{
        return window.location.replace("index.html");
      }
    },
    populateUserViewMoreModal: function(id){
        $("#admin-row-users-view-more").empty();
        RestClient.get(`users/${id}`, function(user){
            $("#admin-row-users-view-more").append(`
                <tr>
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.phone}</td>
                    <td>${user.role}</td>
                </tr>
            `);
        }, function(xhr, status, error){
            console.error("Error fetching user by id: ", error);
        });
    },
    getUserById : function(id) {
        RestClient.get('user_by_id?id='+id, function (data) {
            localStorage.setItem('selected_user', JSON.stringify(data));
            $('input[name="name"]').val(data.name);
            $('input[name="email"]').val(data.email);
            $('input[name="phone"]').val(data.phone);
            $('input[name="role"]').val(data.role);
            $('input[name="id"]').val(data.id);
            $.unblockUI();
        }, function (xhr, status, error) {
            console.error('Error fetching data');
            $.unblockUI();
        });
    }, 
    openUserEditModal: function(id){
        $.blockUI({message: '<h3>Processing...</h3>'});
        $('#exampleUsersModal2').show();
        UserService.getUserById(id);
        $.unblockUI();
    },
    editUser: function(user){
        console.log(user);
        $.blockUI({ message: '<h3>Processing...</h3>' });
        RestClient.patch(`user/${user.id}`, user, function (data) {
            $.unblockUI();
            toastr.success("User edited successfully!");
            UserService.closeModal();
            UserService.generateUsersDatatable();
        }, function (xhr, status, error) {
            UserService.closeModal();
            console.error("Error: ", error);
            $.unblockUI();
        });
    },
    addUser: function(user){
      $.blockUI({message: `<h3>Processing...</h3>`});
      RestClient.post("user", user, function(response){
        $.unblockUI();
        toastr.success("User added successfully!");
        UserService.generateUsersDatatable();
        UserService.closeModal();
      }, function(response){
        UserService.closeModal();
        $.unblockUI();
        toastr.error(response.message);
      });
    },
    openUserConfirmationDialog: function(user){
        user = JSON.parse(user);
        $("#delete-user-body").html(
        "Do you want to delete user: " + user.name
        );
        $("#delete-user-id").val(user.id);
    },
    deleteUser: function () {
        RestClient.delete('user/' + $("#delete-user-id").val(), null, function(response){
            UserService.closeModal();
            toastr.success(response.message);
            UserService.generateUsersDatatable();
        }, function(response){
            UserService.closeModal();
            toastr.error(response.message);
        })
    },
    closeModal: function(){
       const modals = ['#exampleModal4','#exampleUsersModal', '#exampleUsersModal2', '#exampleUsersModal3', '#exampleUsersModal4'];
       modals.forEach(modalId=>{
            const modalEl = document.querySelector(modalId);
            const modal = bootstrap.Modal.getInstance(modalEl);
            if(modal){
                modal.hide();
            }
       });
    }
};