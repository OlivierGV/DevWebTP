import { useEffect } from "@wordpress/element";
import { addFilter, hasFilter, removeFilter } from "@wordpress/hooks";
import { useFormikContext } from "formik";

const debounce = (callback, timeout = 300) => {
	let timer;
	return (...args) => {
		clearTimeout(timer);
		timer = setTimeout(() => {
			callback.apply(this, args);
		}, timeout);
	};
};

const RecipeMBContentsRankMath = () => {
	// Grab values from context.
	const { values } = useFormikContext();

	const debounceRankMathEditor = debounce(() => {
		rankMathEditor.refresh("content");
	}, 300);

	const getContent = (content) => {
		let newContents = content;
		for (const [key, value] of Object.entries(values)) {
			const regex = new RegExp(
				`<!--START${key}START-->.*<!--END${key}END-->`,
				"g"
			);

			newContents = newContents.replace(regex, "");

			if (!value) {
				continue;
			}

			if (Array.isArray(value)) {
				let arrayContent = "";
				switch (key) {
					case "recipeIngredients":
						0 < value.length &&
							value.forEach(({ sectionTitle, ingredients }) => {
								sectionTitle &&
									(arrayContent += " " + sectionTitle);

								Array.isArray(ingredients) &&
									0 < ingredients.length &&
									ingredients.forEach(
										({
											quantity,
											unit,
											ingredient,
											notes,
										}) => {
											quantity &&
												(arrayContent +=
													" " + quantity);

											unit &&
												(arrayContent += " " + unit);

											ingredient &&
												(arrayContent +=
													" " + ingredient);

											notes &&
												(arrayContent += " " + notes);
										}
									);
							});
						break;

					case "recipeInstructions":
						0 < value.length &&
							value.forEach(({ sectionTitle, instruction }) => {
								sectionTitle &&
									(arrayContent += " " + sectionTitle);

								Array.isArray(instruction) &&
									0 < instruction.length &&
									instruction.forEach(
										({
											instructionTitle,
											instruction: inst,
											image,
											image_preview,
											videoURL,
											instructionNotes
										}) => {
											instructionTitle &&
												(arrayContent +=
													" " + instructionTitle);

											inst &&
												(arrayContent +=
													" " + inst);

											image &&
												(arrayContent += " " + image);

											image_preview &&
												(arrayContent +=
													" " + image_preview);

											videoURL &&
												(arrayContent +=
													" " + videoURL);

											instructionNotes &&
												(arrayContent +=
													" " + instructionNotes);
										}
									);
							});
						break;

					case "recipeFAQs":
						0 < value.length &&
							value.forEach(({ question, answer }) => {
								question && (arrayContent += " " + question);
								answer && (arrayContent += " " + answer);
							});
						break;

					default:
						break;
				}

				arrayContent &&
					(newContents += `<!--START${key}START-->${arrayContent}<!--END${key}END-->\n\n`);

				continue;
			}

			newContents += `<!--START${key}START-->${value}<!--END${key}END-->\n\n`;
		}

		return newContents;
	};

	useEffect(() => {
		if ("undefined" !== typeof rankMathEditor && rankMathEditor?.refresh) {
			if (
				hasFilter(
					"rank_math_content",
					"delicious-recipes-rankmath-seo-contents"
				)
			) {
				removeFilter(
					"rank_math_content",
					"delicious-recipes-rankmath-seo-contents"
				);
			}

			addFilter(
				"rank_math_content",
				"delicious-recipes-rankmath-seo-contents",
				getContent,
				11
			);

			debounceRankMathEditor();
		}
	}, [values]);

	return null;
};

export default RecipeMBContentsRankMath;
