"use strict";(self.webpackChunkclient=self.webpackChunkclient||[]).push([[823],{5823:(y,d,r)=>{r.r(d),r.d(d,{LoginModule:()=>v});var u=r(9808),l=r(268),m=r(5861),e=r(4182),c=r(5226),g=r.n(c),o=r(2096),p=r(4485),Z=r(2557);const f=[{path:"",component:(()=>{class t{constructor(n,i,a){this.spinner=n,this.auth=i,this.route=a,this.loginForm=new e.cw({department_number:new e.NI("",[e.kI.required]),password:new e.NI("",[e.kI.required])})}ngOnInit(){$(function(){$("body").removeClass(),$("body").addClass("hold-transition login-page")})}get form_ctl(){return this.loginForm.controls}login(){var n=this;return(0,m.Z)(function*(){if(n.loginForm.valid){n.spinner.show();const i=yield n.auth.login(n.loginForm.value).toPromise().catch(a=>{g().fire("Error",a.message,"error"),n.spinner.hide()});i.access_token?(yield Promise.resolve().then(()=>{localStorage.setItem("ACCESS_TOKEN",i.access_token),localStorage.setItem("USER",JSON.stringify(i.user))}),n.route.navigateByUrl("/home/dashboard")):g().fire("Error",i.error,"error"),n.spinner.hide()}})()}}return t.\u0275fac=function(n){return new(n||t)(o.Y36(p.t2),o.Y36(Z.e),o.Y36(l.F0))},t.\u0275cmp=o.Xpm({type:t,selectors:[["app-login"]],decls:26,vars:1,consts:[[2,"width","auto","height","auto","background","#656788"],[1,"login-box",2,"width","100%","height","auto"],[1,"card","card-outline","card-primary"],[1,"card-header","text-center"],["src","client/assets/logo.png","alt","","width","450",1,"img-fluid"],["src","client/assets/undraw.png","alt","","width","250",1,"img-fluid"],[1,"text-secondary",2,"font-weight","bold"],[1,"card-body"],[1,"login-box-msg","font-weight-bold"],["method","post",3,"formGroup","submit"],[1,"input-group","mb-3"],["type","text","placeholder","Employee Code","formControlName","department_number","required","",1,"form-control"],[1,"input-group-append"],[1,"input-group-text"],[1,"fas","fa-envelope"],["type","password","placeholder","Password","formControlName","password","required","",1,"form-control"],[1,"fas","fa-lock"],[1,"row"],[1,"col-4"],["type","submit",1,"btn","btn-primary","btn-block"]],template:function(n,i){1&n&&(o.TgZ(0,"div",0),o.TgZ(1,"div",1),o.TgZ(2,"div",2),o.TgZ(3,"div",3),o._UZ(4,"img",4),o._UZ(5,"img",5),o.TgZ(6,"h2",6),o._uU(7," Learning Outcome Based Curriculum Framework - (LOCF) "),o.qZA(),o.qZA(),o.TgZ(8,"div",7),o.TgZ(9,"p",8),o._uU(10,"Login to Continue"),o.qZA(),o.TgZ(11,"form",9),o.NdJ("submit",function(){return i.login()}),o.TgZ(12,"div",10),o._UZ(13,"input",11),o.TgZ(14,"div",12),o.TgZ(15,"div",13),o._UZ(16,"span",14),o.qZA(),o.qZA(),o.qZA(),o.TgZ(17,"div",10),o._UZ(18,"input",15),o.TgZ(19,"div",12),o.TgZ(20,"div",13),o._UZ(21,"span",16),o.qZA(),o.qZA(),o.qZA(),o.TgZ(22,"div",17),o.TgZ(23,"div",18),o.TgZ(24,"button",19),o._uU(25," Sign In "),o.qZA(),o.qZA(),o.qZA(),o.qZA(),o.qZA(),o.qZA(),o.qZA(),o.qZA()),2&n&&(o.xp6(11),o.Q6J("formGroup",i.loginForm))},directives:[e._Y,e.JL,e.sg,e.Fj,e.JJ,e.u,e.Q7],styles:[""]}),t})()}];let h=(()=>{class t{}return t.\u0275fac=function(n){return new(n||t)},t.\u0275mod=o.oAB({type:t}),t.\u0275inj=o.cJS({imports:[[l.Bz.forChild(f)],l.Bz]}),t})(),v=(()=>{class t{}return t.\u0275fac=function(n){return new(n||t)},t.\u0275mod=o.oAB({type:t}),t.\u0275inj=o.cJS({imports:[[u.ez,e.UX,h]]}),t})()}}]);