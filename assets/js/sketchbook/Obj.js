

var myObject = (function(x, y){
	
    this.size = 20;
	this.pos = new Vector2(x, y);
	this.dx = 1;
	this.dy = 1;
	
	/*this.vel.x = 0;
	this.vel.y = 0;
	this.force.x = 1;
	this.force.y = 1;*/
	
	this.hange_position = function(){
		this.pos.x = this.pos.x + this.dx;
		this.pos.y = this.pos.y + this.dy;
		
		console.log(this.pos.x + ' - ' + this.pos.y);
	};
	
	this.draw_o = function(x, y){
		ctx.strokeRect(this.pos.x, this.pos.y, this.size, this.size);
	};
});

var Vector2 = function(xx, yy) {
  this.x = xx || 0;
  this.y = yy || 0;
};

myObject.prototype.hange_position_2 = function(cv, ch) {

  // --------------------

  // ====================

  if(this.pos.x + (this.size) >  cv || this.pos.x < 0)
	this.dx = -this.dx;

  if(this.pos.y + (this.size) >  ch || this.pos.y < 0)
	this.dy = -this.dy;

	this.pos.x = this.pos.x + this.dx;
	this.pos.y = this.pos.y + this.dy;
	
	return this;
  // ====================

};