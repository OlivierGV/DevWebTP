import  { parseQuantity, formatQuantity } from './quantities';

window.PrintScripts = {
	init() {
		var recipe = "",
		original_servings = "",
		new_servings = "";

		var searchParams = new URLSearchParams(window.location.search);
		if (searchParams.has("recipe_servings")) {
			new_servings = searchParams.get("recipe_servings");
			new_servings = this.parse(new_servings);
		}

		recipe = parseInt(print_props.recipe);
		original_servings = print_props.original_servings;
		original_servings = this.parse(original_servings);

		if( new_servings != "" && new_servings != original_servings ) {
			this.updateServings(recipe, original_servings, new_servings);
		}
	},
	updateServings(recipe, original_servings, new_servings) {
		const ingredients = document.querySelectorAll( '.ingredient_quantity[data-recipe="' + recipe + '"]' );
		for ( let ingredient of ingredients ) {
			let quantity = ingredient.dataset.original;
			if( quantity != "" ) {
				quantity = this.parse(quantity);
	
				let newQuantity = (quantity / original_servings) * new_servings;
				if ( ! isNaN( newQuantity ) ) {
					newQuantity = this.format( newQuantity );
				}
	
				ingredient.innerHTML = newQuantity;
			}
		}
	},
	parse( quantity ) {
		return parseQuantity( quantity );
	},
	format( quantity ) {
		return formatQuantity( quantity, 2, true );
	},
}
ready(() => {
	window.PrintScripts.init();
});

function ready( fn ) {
    if (document.readyState != 'loading'){
        fn();
    } else {
        document.addEventListener('DOMContentLoaded', fn);
    }
}
