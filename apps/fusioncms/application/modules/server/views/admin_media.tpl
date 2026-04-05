{if $notice}
    <div class="alert alert-success">{$notice}</div>
{/if}

{if $error}
    <div class="alert alert-danger">{$error}</div>
{/if}

<div class="card mb-4">
    <div class="card-header">
        {if $editing}Edit media entry{else}Add media entry{/if}
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data" action="{$url}server/media/{if $editing}save/{$editing.id}{else}create{/if}">
            <div class="form-group row mb-3">
                <label class="col-sm-2 col-form-label" for="title">Title</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" id="title" name="title" value="{$editing.title|default:''}" required>
                </div>
            </div>

            <div class="form-group row mb-3">
                <label class="col-sm-2 col-form-label" for="caption">Caption</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="caption" name="caption" rows="3">{$editing.caption|default:''}</textarea>
                </div>
            </div>

            <div class="form-group row mb-3">
                <label class="col-sm-2 col-form-label" for="type">Type</label>
                <div class="col-sm-4">
                    <select class="form-control" id="type" name="type">
                        <option value="screenshot" {if ($editing.type|default:'') == 'screenshot'}selected{/if}>Screenshot</option>
                        <option value="clip" {if ($editing.type|default:'') == 'clip'}selected{/if}>Clip</option>
                        <option value="media" {if !$editing || ($editing.type|default:'') == 'media'}selected{/if}>Media</option>
                    </select>
                </div>
                <label class="col-sm-2 col-form-label" for="sort_order">Sort order</label>
                <div class="col-sm-4">
                    <input class="form-control" type="number" id="sort_order" name="sort_order" value="{$editing.sort_order|default:100}">
                </div>
            </div>

            <div class="form-group row mb-3">
                <label class="col-sm-2 col-form-label" for="url">Destination URL</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" id="url" name="url" value="{$editing.url|default:''}" placeholder="https://youtube.com/... or /writable/uploads/...">
                    <small class="form-text text-muted">You can paste an external clip/image URL or upload a local media file below.</small>
                </div>
            </div>

            <div class="form-group row mb-3">
                <label class="col-sm-2 col-form-label" for="media_upload">Upload media</label>
                <div class="col-sm-10">
                    <input class="form-control" type="file" id="media_upload" name="media_upload" accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.webm">
                    <small class="form-text text-muted">Optional. Supports screenshots and clip files. If uploaded, it becomes the destination URL automatically.</small>
                </div>
            </div>

            <div class="form-group row mb-3">
                <label class="col-sm-2 col-form-label" for="thumbnail">Thumbnail URL</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" id="thumbnail" name="thumbnail" value="{$editing.thumbnail|default:''}" placeholder="https://... or writable/uploads/server-gallery/...">
                </div>
            </div>

            <div class="form-group row mb-4">
                <label class="col-sm-2 col-form-label" for="thumbnail_upload">Upload thumbnail</label>
                <div class="col-sm-10">
                    <input class="form-control" type="file" id="thumbnail_upload" name="thumbnail_upload" accept=".jpg,.jpeg,.png,.gif,.webp">
                    <small class="form-text text-muted">Optional. If uploaded, it overrides the thumbnail URL for this save.</small>
                </div>
            </div>

            <button type="submit" class="relative font-sans font-normal text-sm inline-flex items-center justify-center leading-5 no-underline h-8 px-3 py-2 space-x-1 border nui-focus transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed hover:enabled:shadow-none text-muted-700 border-muted-300 dark:text-white dark:bg-muted-700 dark:border-muted-600 dark:hover:enabled:bg-muted-600 hover:enabled:bg-muted-50 dark:active:enabled:bg-muted-700/70 active:enabled:bg-muted-100 rounded-md">
                {if $editing}Save entry{else}Create entry{/if}
            </button>

            {if $editing}
                <a href="{$url}server/media" class="relative font-sans font-normal text-sm inline-flex items-center justify-center leading-5 no-underline h-8 px-3 py-2 space-x-1 border nui-focus transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed hover:enabled:shadow-none text-muted-700 border-muted-300 dark:text-white dark:bg-muted-700 dark:border-muted-600 dark:hover:enabled:bg-muted-600 hover:enabled:bg-muted-50 dark:active:enabled:bg-muted-700/70 active:enabled:bg-muted-100 rounded-md">Cancel</a>
            {/if}
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Media wall entries ({count($items)})</div>
    <div class="card-body">
        <table class="table table-responsive-md table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Preview</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Sort</th>
                    <th>Destination</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                {if $items}
                    {foreach from=$items item=item}
                        <tr>
                            <td>{$item.id}</td>
                            <td>
                                {if $item.thumbnail_url}
                                    <img src="{$item.thumbnail_url}" alt="{$item.title}" style="width:88px;height:52px;object-fit:cover;border-radius:8px;">
                                {else}
                                    <span class="text-muted">No thumbnail</span>
                                {/if}
                            </td>
                            <td>
                                <strong>{$item.title}</strong><br>
                                <small>{$item.caption|truncate:80}</small>
                            </td>
                            <td>{$item.type}</td>
                            <td>{$item.sort_order}</td>
                            <td><a href="{$item.media_url}" target="_blank" rel="noopener noreferrer">Open</a></td>
                            <td style="text-align:center;">
                                <a class="relative font-sans font-normal text-sm inline-flex items-center justify-center leading-5 no-underline h-8 px-3 py-2 space-x-1 border nui-focus transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed hover:enabled:shadow-none text-muted-700 border-muted-300 dark:text-white dark:bg-muted-700 dark:border-muted-600 dark:hover:enabled:bg-muted-600 hover:enabled:bg-muted-50 dark:active:enabled:bg-muted-700/70 active:enabled:bg-muted-100 rounded-md" href="{$url}server/media/index/{$item.id}">Edit</a>
                                <form method="post" action="{$url}server/media/delete/{$item.id}" style="display:inline-block;" onsubmit="return confirm('Delete this media entry?');">
                                    <button type="submit" class="relative font-sans font-normal text-sm inline-flex items-center justify-center leading-5 no-underline h-8 px-3 py-2 space-x-1 border nui-focus transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed hover:enabled:shadow-none text-muted-700 border-muted-300 dark:text-white dark:bg-muted-700 dark:border-muted-600 dark:hover:enabled:bg-muted-600 hover:enabled:bg-muted-50 dark:active:enabled:bg-muted-700/70 active:enabled:bg-muted-100 rounded-md">Delete</button>
                                </form>
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="7">No media entries yet. Add screenshots or clip cards here, and they will appear on the public server page automatically.</td>
                    </tr>
                {/if}
            </tbody>
        </table>
    </div>
</div>
